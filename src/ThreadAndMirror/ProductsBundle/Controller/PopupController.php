<?php

namespace ThreadAndMirror\ProductsBundle\Controller;

use Stems\CoreBundle\Controller\BaseRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ThreadAndMirror\ProductsBundle\Entity\SectionProduct;
use ThreadAndMirror\ProductsBundle\Entity\SectionProductGalleryProduct;
use ThreadAndMirror\ProductsBundle\Form\SectionProductGalleryProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use ThreadAndMirror\ProductsBundle\Form\SectionProductType;


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
	 * @Route("popup/products/update-product-gallery-product/{id}", name="thread_products_popup_update_product_gallery_product")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param  SectionProductGalleryProduct     $product
	 * @param  Request
	 * @return JsonResponse
	 */
	public function updateProductGalleryProductAction(SectionProductGalleryProduct $product, Request $request)
	{
		// Build the form
		$form = $this->createForm(new SectionProductGalleryProductType(), $product);

		// Get the html for the popup
		$html = $this->renderView('ThreadAndMirrorProductsBundle:Popup:updateProductGalleryProduct.html.twig', [
			'product'	=> $product,
			'title'		=> 'Edit Product '.$product->getHeading(),
			'form'		=> $form->createView(),
		]);

		return $this->addHtml($html)->success()->sendResponse();
	}

	/**
	 * Build a popup to edit a product gallery product
	 *
	 * @Route("popup/products/update-product-section/{id}", name="thread_products_popup_update_product_section")
	 * @Security("has_role('ROLE_ADMIN')")
	 *
	 * @param  SectionProduct       $product
	 * @param  Request
	 * @return JsonResponse
	 */
	public function updateProductSectionAction(SectionProduct $section, Request $request)
	{
		$em   = $this->getDoctrine()->getManager();
		$link = $em->getRepository('StemsBlogBundle:Section')->findOneBy(['entity' => $section->getId(), 'type' => 'product']);
		$form = $this->createForm(new SectionProductType($link), $section);

		$html = $this->renderView('ThreadAndMirrorProductsBundle:Popup:updateProductSection.html.twig', [
			'section'	=> $section,
			'title'		=> 'Edit Product '.$section->getName(),
			'form'		=> $form->createView(),
		]);

		return $this->addHtml($html)->success()->sendResponse();
	}
}