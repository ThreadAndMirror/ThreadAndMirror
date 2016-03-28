<?php

namespace ThreadAndMirror\BlogBundle\Controller;

use Stems\CoreBundle\Controller\BaseRestController,
	Symfony\Component\HttpFoundation\Request,
	ThreadAndMirror\BlogBundle\Entity\SectionProductGalleryProduct,
	ThreadAndMirror\BlogBundle\Form\SectionProductGalleryProductType,
	Stems\MediaBundle\Entity\Image,
	Stems\MediaBundle\Form\ImageType;


class PopupController extends BaseRestController
{
	/**
	 * Build a popup to set the feature image of a blog post
	 *
	 * @param  integer 		$id 	The ID of the blog post
	 * @param  Request
	 * @return JsonResponse
	 */
	public function setFeatureImageAction($id, Request $request)
	{
		// Get the blog post and existing image
		$em    = $this->getDoctrine()->getManager();
		$post  = $em->getRepository('ThreadAndMirrorBlogBundle:Post')->find($id);

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