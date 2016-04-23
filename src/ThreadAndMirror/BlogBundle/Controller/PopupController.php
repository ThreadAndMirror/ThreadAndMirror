<?php

namespace ThreadAndMirror\BlogBundle\Controller;

use Stems\CoreBundle\Controller\BaseRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stems\MediaBundle\Entity\Image;
use ThreadAndMirror\BlogBundle\Entity\Post;

/**
 * @Route("/popup/blog", name="thread_blog_popup")
 */
class PopupController extends BaseRestController
{
	/**
	 * Build a popup to set the feature image of a blog post
	 *
	 * @param  integer 		$id 	The ID of the blog post
	 * @param  Request
	 *
	 * @return JsonResponse
	 *
	 * @Route("/set-feature-image/{id}", name="thread_blog_popup_set_feature_image")
	 */
	public function setFeatureImageAction(Post $post, Request $request)
	{
		// Get the blog post and existing image
		$em = $this->getDoctrine()->getManager();

		if ($post->getImage()) {
			$image = $em->getRepository('StemsMediaBundle:Image')->find($post->getImage());
		} else {
			$image = new Image();
			$image->setCategory('blog');
		}

		// Build the form 
		$form = $this->createForm('media_image_type', $image);

		// Get the html for the popup
		$html = $this->renderView('ThreadAndMirrorBlogBundle:Popup:setFeatureImage.html.twig', array(
			'post'		=> $post,
			'existing'	=> rawurldecode($request->query->get('existing')),
			'title'		=> $post->getImage() ? 'Change Feature Image' : 'Add Feature Image',
			'form'		=> $form->createView(),
		));

		return $this->addHtml($html)->success('The popup was successfully created.')->sendResponse();
	}
}