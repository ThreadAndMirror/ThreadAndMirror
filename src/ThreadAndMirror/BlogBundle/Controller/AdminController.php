<?php

namespace ThreadAndMirror\BlogBundle\Controller;

use Stems\CoreBundle\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Request;
use ThreadAndMirror\BlogBundle\Form\AdminPostType;
use ThreadAndMirror\BlogBundle\Entity\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/admin/blog", name="thread_blog_admin")
 */
class AdminController extends BaseAdminController
{
	/**
	 * Render the dialogue for the module's dashboard entry in the admin panel
	 */
	public function dashboardAction()
	{
		// Get the number of unmoderated comments
		$comments = $this->em->getRepository('ThreadAndMirrorBlogBundle:Comment')->findBy(array('moderated' => false, 'deleted' => false));
		$comments = count($comments); 

		return $this->render('ThreadAndMirrorBlogBundle:Admin:dashboard.html.twig', array(
			'comments' => $comments,
		));
	}

	/**
	 * Build the sitemap entries for the bundle
	 */
	public function sitemapAction()
	{
		// The slug used for the blog (eg. news, blog or magazine)
		$slug = 'magazine';

		// Get the posts
		$posts = $this->em->getRepository('ThreadAndMirrorBlogBundle:Post')->findPublishedPostsByCategory('articles', 9999);

		return $this->render('ThreadAndMirrorBlogBundle:Admin:sitemap.html.twig', array(
			'slug' 		=> $slug,
			'posts'		=> $posts,
		));
	}

	/**
	 * Overview of all magazine posts
	 *
	 * @Route("/", name="thread_blog_admin_index")
	 * @Template()
	 */
	public function indexAction()
	{		
		// Get all undeleted articles
		$posts = $this->em->getRepository('ThreadAndMirrorBlogBundle:Post')->findBy(['deleted' => false], ['created' => 'DESC']);

		return [
			'posts' => $posts,
		];
	}

	/**
	 * Create a post, using a template if defined
	 *
	 * @Route("/create", name="thread_blog_admin_create")
	 */
	public function createAction(Request $request)
	{
		$em = $this->getDoctrine()->getEntityManager();

		// Create a new post for persisting, so we already have an id for adding sections etc.
		$post = new Post();
		$post->setAuthor($this->getUser()->getId());
		$category = $this->em->getRepository('ThreadAndMirrorBlogBundle:Category')->find(1);
		$post->setCategory($category);

		$this->em->persist($post);
		
		// If a title was posted then use it
		$request->get('title') and $post->setTitle($request->get('title'));
		$this->em->flush();

		// Save all the things
		$this->em->flush();

		// Redirect to the edit page for the new post
		return $this->redirect($this->generateUrl('thread_blog_admin_edit', array('id' => $post->getId())));
	}

	/**
	 * Edit a blog post
	 *
	 * @Route("/{id}/edit", name="thread_blog_admin_edit")
	 * @Template()
	 */
	public function editAction(Request $request, Post $post)
	{
		// Load the section management service
		$sectionHandler = $this->get('stems.core.sections.manager')->setBundle('blog');

		// Throw an error if the post could not be found
		if (!$post) {
			$request->getSession()->getFlashBag()->set('error', 'The requested post could not be found.');
			return $this->redirect($this->generateUrl('thread_blog_admin_index'));
		}

		// Get the available section types
		$types = $this->container->getParameter('stems.sections.available');

		// Create the edit form and forms for the sections
		$form = $this->createForm(new AdminPostType(), $post);
		$sectionForms = $sectionHandler->getEditors($post->getSections());

		// Handle the form submission
		if ($request->getMethod() == 'POST') {

			// Validate the submitted values
			$form->bind($request);

			//if ($form->isValid()) {

				// Update the post in the database
				$post->setNew(false);
				$post->setTitle(stripslashes($post->getTitle()));
				$post->setExcerpt(stripslashes($post->getExcerpt()));
				$post->setContent(stripslashes($post->getContent()));
				$post->setAuthor($this->getUser()->getId());

				// Order the sections, attached to the page and save their values
				$position = 1;

				foreach ($request->get('sections', []) as $section) {
					
					// Attach and update order
					$sectionEntity = $this->em->getRepository('ThreadAndMirrorBlogBundle:Section')->find($section);
					$sectionEntity->setPost($post);
					$sectionEntity->setPosition($position);

					$sectionEntity->setX($request->get('sections_x')[$section]);
					$sectionEntity->setY($request->get('sections_y')[$section]);
					$sectionEntity->setWidth($request->get('sections_width')[$section]);
					$sectionEntity->setHeight($request->get('sections_height')[$section]);
					$sectionEntity->setSpan($request->get('sections_span')[$section]);
					$sectionEntity->setPinned($request->get('sections_pinned')[$section]);

					// Get all form fields relevant to the section...
					foreach ($request->request->all() as $parameter => $value) {
						// Strip the section id from the parameter group and save if it matches
						$explode = explode('_', $parameter);
						$parameterId = reset($explode);
						$parameterId == $sectionEntity->getId() and $sectionParameters = $value;
					}

					// ...then process and update the entity
					$sectionHandler->saveSection($sectionEntity, $sectionParameters, $request);
					$this->em->persist($sectionEntity);

					$position++;
				}

				// If there were no errors then save the entity, otherwise display the save errors
				// if ($sectionHandler->getSaveErrors()) {
					
					$this->em->persist($post);
					$this->em->flush();
					$request->getSession()->getFlashBag()->set('success', 'The post "'.$post->getTitle().'" has been updated.');

					return $this->redirect($this->generateUrl('thread_blog_admin_index'));

				// } else {
				// 	$request->getSession()->getFlashBag()->set('error', 'Your request was not processed as errors were found.');
				// 	$request->getSession()->getFlashBag()->set('debug', '');
				// }
			//}
		}

		return $this->render('ThreadAndMirrorBlogBundle:Admin:edit.html.twig', array(
			'form'			=> $form->createView(),
			'sectionForms'	=> $sectionForms,
			'types'			=> $types['blog'],
			'post' 			=> $post,
		));
	}

	/**
	 * Delete a post
	 *
	 * @Route("/{id}/delete", name="thread_blog_admin_delete")
	 */
	public function deleteAction(Request $request, Post $post)
	{
		// Delete the post if was found
		$name = $post->getTitle();
		$post->setDeleted(true);
		$this->em->persist($post);
		$this->em->flush();

		// Return the success message
		$request->getSession()->getFlashBag()->set('success', 'The post "'.$name.'" was successfully deleted!');

		return $this->redirect($this->generateUrl('thread_blog_admin_index'));
	}

	/**
	 * Publish/unpublish a post
	 *
	 * @Route("/{id}/publish", name="thread_blog_admin_publish")
	 */
	public function publishAction(Request $request, Post $post)
	{
		// Set the post to published/unpublished
		if ($post->getStatus() == 'Draft') {
			$post->setStatus('Published');
			$post->setPublished(new \DateTime());
			$request->getSession()->getFlashBag()->set('success', 'The post "'.$post->getTitle().'" was successfully published!');
		} else {
			$post->setStatus('Draft');
			$request->getSession()->getFlashBag()->set('success', 'The post "'.$post->getTitle().'" was successfully unpublished!');
		}

		$this->em->persist($post);
		$this->em->flush();

		return $this->redirect($this->generateUrl('thread_blog_admin_index'));
	}

	/**
	 * A listing of all unmoderated comments
	 */
	public function commentsAction()
	{		
		// Get all unmoderated comments
		$em       = $this->getDoctrine()->getEntityManager();
		$comments = $this->em->getRepository('ThreadAndMirrorBlogBundle:Comment')->findBy(array('deleted' => false, 'moderated' => false), array('created' => 'DESC'));

		return $this->render('ThreadAndMirrorBlogBundle:Admin:comments.html.twig', array(
			'comments' 	=> $comments,
		));
	}

	/**
	 * Moderate a comment
	 *
	 * @param  integer 	$id  	The ID of the comment
	 * @param  Request 
	 */
	public function moderateCommentAction(Request $request, $id)
	{
		// Get the comment
		$em      = $this->getDoctrine()->getEntityManager();
		$comment = $this->em->getRepository('ThreadAndMirrorBlogBundle:Comment')->findOneBy(array('id' => $id, 'deleted' => false));

		if ($comment) {

			// Set the comment to moderated
			$comment->setModerated(true);
			$request->getSession()->getFlashBag()->set('success', 'The comment was successfully authorised!');

			$this->em->persist($comment);
			$this->em->flush();

		} else {
			$request->getSession()->getFlashBag()->set('error', 'The requested comment could not be moderated as it does not exist in the database.');
		}

		return $this->redirect($this->generateUrl('thread_blog_admin_index'));
	}

	/**
	 * Delete a comment
	 *
	 * @param  integer 	$id  	The ID of the post comment
	 * @param  Request 
	 */
	public function deleteCommentAction(Request $request, $id)
	{
		// Get the comment
		$em   = $this->getDoctrine()->getEntityManager();
		$comment = $this->em->getRepository('ThreadAndMirrorBlogBundle:Comment')->findOneBy(array('id' => $id, 'deleted' => false));

		if ($comment) {

			// Delete the comment if was found
			$comment->setDeleted(true);
			$this->em->persist($comment);
			$this->em->flush();

			// Return the success message
			$request->getSession()->getFlashBag()->set('success', 'The comment was successfully deleted!');

		} else {
			$request->getSession()->getFlashBag()->set('error', 'The requested comment could not be deleted as it does not exist in the database.');
		}

		return $this->redirect($this->generateUrl('thread_blog_admin_index'));
	}
}
