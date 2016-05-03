<?php

namespace ThreadAndMirror\BlogBundle\Controller;

use Stems\CoreBundle\Controller\BaseRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ThreadAndMirror\BlogBundle\Entity\Post;
use ThreadAndMirror\BlogBundle\Entity\Section;
use ThreadAndMirror\BlogBundle\Entity\SectionProductGalleryProduct;
use ThreadAndMirror\BlogBundle\Form\SectionProductGalleryProductType;
use Stems\MediaBundle\Entity\Image;
use Stems\MediaBundle\Form\ImageType;
use ThreadAndMirror\BlogBundle\Entity\Comment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/rest/blog", name="thread_blog_rest")
 */
class RestController extends BaseRestController
{
	/**
	 * Returns form html for the requested section type
	 *
	 * @param  integer 	    $offset 	The amount of posts already loaded
	 * @param  integer 	    $chunk 		The maximum amount of posts to get
	 *
	 * @Route("/get-more-posts/{offset}", name="thread_blog_rest_more_posts")
	 */
	public function getMorePostsAction($offset = 6, $chunk = 6)
	{
		// Get more of the blog posts for the view
		$em    = $this->getDoctrine()->getManager();
		$posts = $em->getRepository('ThreadAndMirrorBlogBundle:Post')->findLatest($chunk, $offset);

		// Render the html for the posts
		$html = '';

		foreach ($posts as $post) {
			$html .= $this->renderView('ThreadAndMirrorBlogBundle:Rest:post.html.twig', [
				'post' 	=> $post
			]);
		}
		
		// Let the ajax response know when there's no more additional posts to load
		count($posts) < $chunk and $this->setCallback('stopLoading');

		return $this->addHtml($html)->success()->sendResponse();
	}

	/**
	 * Processes the submission of an add comment form
	 *
	 * @param  integer 		$post 		The ID of the post to add the comment to
	 * @param  Request 		$request
	 * @return JsonResponse
	 */
	public function addCommentAction($post, Request $request)
	{
		// Get the post
		$em   = $this->getDoctrine()->getManager();
		$post = $em->getRepository('ThreadAndMirrorBlogBundle:Post')->find($post);

		// Build the comment form
		$comment 	  = new Comment();
		$form         = $this->createForm('blog_comment', $comment);

		// Process the submission
		if ($request->getMethod() == 'POST') {

			// Validate the submitted values
			$form->bind($request);

			if ($form->isValid()) {

				// Set the user ID if we require login for commenting
				if ($this->container->getParameter('threadandmirror.blog.comments.require_login')) {
					if ($this->get('security.context')->isGranted('ROLE_USER')) {
						$comment->setAuthor($this->getUser()->getId());
					} else {
						return $this->error('You need to be logged in to post a comment.', true)->sendResponse();
					}
				}

				// Attach to the post and save
				$comment->setPost($post);
				$em->persist($comment);
				$em->flush();

				// Return the rendered comment
				$html = $this->renderView('ThreadAndMirrorBlogBundle:Rest:comment.html.twig', array(
					'image'		 => $image,
					'created'	 => true,
					'moderation' => $this->container->getParameter('threadandmirror.blog.comments.moderated'),
				));

				return $this->addHtml($html)->setCallback('commentAdded')->success()->sendResponse();
			} else {
				// Add the validation errors to the response
				$this->addValidationErrors($form);
				
				return $this->setCallback('commentNotAdded')->error()->sendResponse();
			}
		}

		return $this->error('Unauthorised Method.')->sendResponse();
	}

	/**
	 * Returns form html for the requested section type
	 *
	 * @param  integer 	$type 	Section type id
	 * @return JsonResponse
	 *
	 * @Route("/add-section-type/{type}", name="thread_blog_rest_add_section_type")
	 */
	public function addSectionTypeAction($type)
	{
		// Get the section type
		$em    	   = $this->getDoctrine()->getManager();
		$available = $this->container->getParameter('stems.sections.available');

		// Create a new section of the specified type
		$class = $available['blog'][$type]['class'];
		$section = new $class();

		$em->persist($section);
		$em->flush();
		
		// Create the section linkage
		$link = new Section();
		$link->setType($type);
		$link->setEntity($section->getId());
		$em->persist($link);
		$em->flush();

		// Get the form html
		$sectionHandler = $this->get('stems.core.sections.manager')->setBundle('blog');

		$html = $section->editor($sectionHandler, $link);
		// Store the section id for use in the response handler
		$meta = array('section' => $link->getId());

		return $this->addHtml($html)->addMeta($meta)->success()->sendResponse();
	}

	/**
	 * Removes the specified section and its linkage
	 *
	 * @param  integer 		$id 	Section id
	 *
	 * @return JsonResponse
	 *
	 * @Route("/remove-section/{id}", name="thread_blog_rest_remove_section")
	 */
	public function removeSectionAction($id)
	{
		try
		{
			// Get the section linkage and the specific section
			$em      = $this->getDoctrine()->getManager();
			$link    = $em->getRepository('ThreadAndMirrorBlogBundle:Section')->find($id);
			$types   = $this->container->getParameter('stems.sections.available');
			$section = $em->getRepository($types['blog'][$link->getType()]['entity'])->find($link->getEntity());

			$em->remove($section);
			$em->remove($link);
			$em->flush();

			return $this->success('Section deleted.')->sendResponse();
		}
		catch (\Exception $e) 
		{
			return $this->error($e->getMessage())->sendResponse();
		}
	}

	/**
	 * Updates the feature image for a blog post
	 *
	 * @param  integer 		$id 		The ID of the Product Gallery Section to add the image to
	 * @param  Request 		$request
	 *
	 * @return JsonResponse
	 *
	 * @Route("/set-feature-image/{id}", name="thread_blog_rest_set_featureimage")
	 */
	public function setFeatureImageAction(Post $post, Request $request)
	{
		// Get the blog post and existing image
		$em    = $this->getDoctrine()->getManager();

		if ($post->getImage()) {
			$image = $em->getRepository('StemsMediaBundle:Image')->find($post->getImage());
		} else {
			$image = new Image();
			$image->setCategory('blog');
		}

		// Build the form and handle the request
		$form = $this->createForm('media_image_type', $image);

		if ($form->bind($request)->isValid()) {

			// Upload the file and save the entity
			$image->doUpload();
			$em->persist($image);
			$em->flush();

			$meta = array('id' => $image->getId());

			// Get the html for updating the feature image
			$html = $this->renderView('ThreadAndMirrorBlogBundle:Rest:setFeatureImage.html.twig', array(
				'post'	=> $post,
				'image'	=> $image,
			));

			return $this->addHtml($html)->setCallback('updateFeatureImage')->addMeta($meta)->success('Image updated.')->sendResponse();
		} else {
			return $this->error('Please choose an image to upload.', true)->sendResponse();
		}
	}
}
