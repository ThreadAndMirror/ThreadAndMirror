<?php

namespace ThreadAndMirror\ProductsBundle\Controller;

use Stems\CoreBundle\Controller\BaseRestController,
	Symfony\Component\HttpFoundation\Request,
	ThreadAndMirror\ProductsBundle\Entity\SectionProductGalleryProduct,
	ThreadAndMirror\ProductsBundle\Form\SectionProductGalleryProductType,
	Stems\MediaBundle\Entity\Image,
	Stems\MediaBundle\Form\ImageType;


class PopupController extends BaseRestController
{
	/**
	 * Build a popup to manually add a product to a product gallery section, created a skeleton entity in the first place.
	 *
	 * @param  integer 		$id 	The ID of the Product Gallery Section to add the image to
	 * @param  Request
	 * @return JsonResponse
	 */
	public function addProductGalleryProductAction($id, Request $request)
	{
		// Get the section for the field id
		$em      = $this->getDoctrine()->getManager();
		$section = $em->getRepository('ThreadAndMirrorProductsBundle:SectionProductGallery')->find($id);

		// Create the product
		$image = new SectionProductGalleryProduct();
		$image->setSectionProductGallery($section);
		$image->setHeading('New Product');
		$image->setThumbnail('image.jpg');
		$image->setImage('image.jpg');

		$em->persist($image);
		$em->flush();

		// Build the form 
		$form = $this->createForm(new SectionProductGalleryProductType(), $image);

		// Get the html for the popup
		$html = $this->renderView('ThreadAndMirrorProductsBundle:Popup:updateProductGalleryProduct.html.twig', array(
			'product'	=> $image,
			'title'		=> 'Add a New Product Manually',
			'form'		=> $form->createView(),
		));

		return $this->addHtml($html)->success('The popup was successfully created.')->sendResponse();
	}

	/**
	 * Build a popup to edit a product gallery product
	 *
	 * @param  integer 		$id 	The ID of the Product Gallery Product
	 * @param  Request
	 * @return JsonResponse
	 */
	public function updateProductGalleryProductAction($id, Request $request)
	{
		// Get the product
		$em    = $this->getDoctrine()->getManager();
		$image = $em->getRepository('ThreadAndMirrorProductsBundle:SectionProductGalleryProduct')->find($id);

		// Build the form 
		$form = $this->createForm(new SectionProductGalleryProductType(), $image);

		// Get the html for the popup
		$html = $this->renderView('ThreadAndMirrorProductsBundle:Popup:updateProductGalleryProduct.html.twig', array(
			'product'	=> $image,
			'title'		=> 'Edit Product '.$image->getHeading(),
			'form'		=> $form->createView(),
		));

		return $this->addHtml($html)->success('The popup was successfully created.')->sendResponse();
	}
}