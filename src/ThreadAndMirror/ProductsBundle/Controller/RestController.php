<?php

namespace ThreadAndMirror\ProductsBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Stems\CoreBundle\Controller\BaseRestController;
use ThreadAndMirror\ProductsBundle\Entity\Pick;
use ThreadAndMirror\ProductsBundle\Entity\Outfit;
use ThreadAndMirror\ProductsBundle\Entity\Product;
use ThreadAndMirror\ProductsBundle\Entity\SectionProduct;
use ThreadAndMirror\ProductsBundle\Entity\SectionProductGallery;
use ThreadAndMirror\ProductsBundle\Entity\SectionProductGalleryProduct;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use ThreadAndMirror\ProductsBundle\Exception\ProductParseException;
use ThreadAndMirror\ProductsBundle\Form\SectionProductGalleryType;
use ThreadAndMirror\ProductsBundle\Form\SectionProductType;

class RestController extends BaseRestController
{
	/**
	 * Adds the requested product to the current user's wishlist, including the size if it was requested
	 */
	public function addToWishlistAction($id, Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$size = $request->get('size');

		// get the user's wishlist and the requested product
		$wishlist = $em->getRepository('ThreadAndMirrorProductsBundle:Wishlist')->findOneByOwner($this->getUser()->getId());
		$product = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->find($id);

		// check whether the product is already on the users wishlist
		$pick = $em->getRepository('ThreadAndMirrorProductsBundle:Pick')->findOneBy(array('product' => $product, 'wishlist' => $wishlist));

		if (!$pick) {
			// create a new pick to link the product to the wishlist
			$pick = new Pick();
			$pick->setWishlist($wishlist);
			$pick->setProduct($product);
			$size and $pick->setSizes(array($size));

			$em->persist($pick);
			$em->flush();

			return new JsonResponse(array(
				'success' => true,
				'message' => $product->getName() . ' has been added to your wishlist!',
			));
		} else {
			return new JsonResponse(array(
				'success' => false,
				'message' => $product->getName() . ' is already on your wishlist!',
			));
		}
	}

	/**
	 * Removes the requested pick from the current user's wishlist
	 */
	public function removeFromWishlistAction($id)
	{
		$em = $this->getDoctrine()->getManager();

		// get the pick
		$pick = $em->getRepository('ThreadAndMirrorProductsBundle:Pick')->find($id);

		try {
			// fail if the pick doesn't belong to the current user
			if ($pick->getWishlist()->getOwner() != $this->getUser()->getId()) {
				return new JsonResponse(array(
					'success' => false,
					'message' => 'The requested wishlist item wasn\'t found.'
				));
			}
		} catch (\Exception $e) {
			// fail if the pick wasn't found or any other error occurred
			return new JsonResponse(array(
				'success' => false,
				'message' => 'The requested wishlist item wasn\'t found.'
			));
		}

		$em->remove($pick);
		$em->flush();

		return new JsonResponse(array(
			'success' => true,
			'message' => $pick->getProduct()->getName() . ' has been removed from your wishlist!',
		));
	}

	/**
	 * Adds the requested product to an outfit
	 */
	public function addToOutfitAction($id, $outfit)
	{
		$em = $this->getDoctrine()->getManager();

		// get the user's outfit and the requested product
		$outfit = $em->getRepository('ThreadAndMirrorProductsBundle:Outfit')->find($outfit);
		$product = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->find($id);

		// fail if the pick doesn't belong to the current user
		if ($outfit->getOwner() != $this->getUser()->getId()) {
			return new JsonResponse(array(
				'success' => false,
				'message' => 'The requested outfit doesn\'t belong to you.'
			));
		}

		// fail if the pick is already in the outfit
		// if ($pick->getOutfit->getId() == $outfit)) {
		// 	return new JsonResponse(array(
		// 		'success'   => false,
		// 		'message' 	=> 'The requested outfit doesn\'t belong to you.'
		// 	));
		// }

		// create a new pick to link the product to the outfit
		$pick = new Pick();
		$pick->setOutfit($outfit);
		$pick->setProduct($product);

		$em->persist($pick);
		$em->flush();

		return new JsonResponse(array(
			'success' => true,
			'message' => $product->getName() . ' has been added to your outfit ' . $outfit->getTitle() . '.',
		));
	}

	/**
	 * Saves the current filters as the users favourite
	 */
	public function saveFavouriteFiltersAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$profile = $em->getRepository('ThreadAndMirrorProductsBundle:Profile')->findOneByUser($this->getUser()->getId());

		// return error if the user isn't logged in
		if (!$profile) {
			return new JsonResponse(array(
				'success' => false,
				'message' => 'You need to be signed in to save your favourite filters.'
			));
		} else {
			// get the filter config that's stored in the session
			$filters = $this->get('threadandmirror.product.filter')->process($request);

			// save to the user's profile
			$profile->setFilters(serialize($filters));
			$em->persist($profile);
			$em->flush();

			return new JsonResponse(array(
				'success' => true,
				'message' => 'Your favourite filters have been saved.',
			));
		}
	}

	/**
	 * Attempt to add a product to the user's wishlist using the URL provided
	 */
	public function addProductFromUrlAction(Request $request)
	{
		// attempt to parse the product
		$em = $this->getDoctrine()->getManager();
		$product = $this->get('threadandmirror.products.service.product')->getProductFromUrl($request->get('url'));

		// if we manage to parse a product from the url then handle persisting
		if (is_object($product)) {

			// Persist
			$em->persist($product);

			// create a pick from the product
			$wishlist = $em->getRepository('ThreadAndMirrorProductsBundle:Wishlist')->findOneByOwner($this->getUser()->getId());
			$pick = new Pick();
			$pick->setWishlist($wishlist);
			$pick->setProduct($product);

			$em->persist($pick);
			$em->flush();

			// success response
			return new JsonResponse(array(
				'success' => true,
				'message' => 'The product was successfully saved to your wishlist.'
			));
		} else {
			// error response
			return new JsonResponse(array(
				'success' => false,
				'message' => 'We could not load a product using that link.'
			));
		}
	}

	/**
	 * Sets the requested outfit as a featured outfit, as long as the requesting user is an admin and it belongs to them
	 */
	public function setOutfitFeaturedAction($id)
	{

		// fail if the user is not admin level or higher
		if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
			return new JsonResponse(array(
				'success' => false,
				'message' => 'Permission denied.'
			));
		}

		// get the outfit
		$em = $this->getDoctrine()->getManager();
		$outfit = $em->getRepository('ThreadAndMirrorProductsBundle:Outfit')->find($id);

		// fail if the outfit doesn't belong to the current user
		if ($outfit->getOwner() != $this->getUser()->getId()) {
			return new JsonResponse(array(
				'success' => false,
				'message' => 'The requested outfit doesn\'t belong to you.'
			));
		}

		// set the featured date as now
		$outfit->setFeatured(new \DateTime());
		$em->persist($outfit);
		$em->flush();

		return new JsonResponse(array(
			'success' => true,
			'message' => 'The outfit ' . $outfit->getTitle() . ' is now marked as featured.',
		));
	}

	/**
	 * Creates a new outfit
	 */
	public function addNewOutfitAction(Request $request)
	{

		// fail if the user is not logged in
		if (!$this->get('security.context')->isGranted('ROLE_USER')) {
			return new JsonResponse(array(
				'success' => false,
				'message' => 'Permission denied.',
				'html'    => '',
			));
		}

		// fail if the user didn't give the outfit a name
		$name = $request->get('name');

		if (empty($name)) {
			return new JsonResponse(array(
				'success' => false,
				'message' => 'You need to give the outfit a name before it can be created!',
				'html'    => '',
			));
		}

		// create the outfit
		$em = $this->getDoctrine()->getManager();
		$outfit = new Outfit();

		$outfit->setOwner($this->getUser()->getId());
		$outfit->setTitle($request->get('name'));
		$outfit->setCaption($request->get('description'));

		$em->persist($outfit);
		$em->flush();

		// get the html for the new outfit to add to the page
		$html = $this->renderView('ThreadAndMirrorProductsBundle:Rest:outfit.html.twig', array(
			'outfit' => $outfit,
		));

		return new JsonResponse(array(
			'success' => true,
			'message' => 'The outfit ' . $outfit->getTitle() . ' has been created.',
			'html'    => $html,
		));
	}

	/**
	 * View product details
	 */
	public function viewProductAction($id, Request $request)
	{
		// load the product
		$em = $this->getDoctrine()->getManager();
		$product = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->find($id);

		// check if a product was found
		if (is_object($product)) {

			$refresh = new \DateTime();
			$refresh->modify('-15 minutes');

			// if the product hasn't been fully parsed then force an update
			if (!$product->getFullyParsed() || $refresh > $product->getChecked()) {
				$product = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->forceUpdate($product);
			}

			// check if the product already exists before we save it
			$html = $this->renderView('ThreadAndMirrorProductsBundle:Rest:product.html.twig', array(
				'product' => $product,
			));

			// success response
			return new JsonResponse(array(
				'success' => true,
				'html'    => $html,
			));
		} else {
			// error response
			return new JsonResponse(array(
				'success' => false,
				'message' => 'There was a problem loading the product\'s details',
			));
		}
	}

	/**
	 * Update a product gallery product, both generated and manually added
	 *
	 * @param  integer $id The ID of the Product Gallery Section to update
	 * @param  Request
	 * @return JsonResponse
	 */
	public function updateProductGalleryProductAction($id, Request $request)
	{
		// Get the url from the query parameter and attempt to parse the product
		$em = $this->getDoctrine()->getManager();
		$image = $em->getRepository('StemsBlogBundle:SectionProductGalleryProduct')->find($id);

		$data = json_decode($request->getContent());

		// If the product exists, then handle the request
		if (is_object($image)) {

			// Update the product
			$image->setHeading($request->request->get('section_productgalleryproduct_type')['heading']);
			$image->setCaption($request->request->get('section_productgalleryproduct_type')['caption']);
			$image->setUrl($request->request->get('section_productgalleryproduct_type')['url']);
			$image->setThumbnail($request->request->get('section_productgalleryproduct_type')['thumbnail']);
			$image->setImage($request->request->get('section_productgalleryproduct_type')['image']);

			$em->persist($image);
			$em->flush();

			// Get the associated section linkage to tag the fields with the right id
			$link = $em->getRepository('StemsBlogBundle:Section')->findOneByEntity($image->getSectionProductGallery()->getId());

			// Get the html for the product gallery item and to add to the page
			$html = $this->renderView('StemsBlogBundle:Rest:productGalleryProduct.html.twig', array(
				'product' => $image,
				'section' => $image->getSectionProductGallery(),
				'link'    => $link,
			));

			// Store the section and product id for use in the response handler
			$this->addMeta(array(
				'section' => $link->getId(),
				'product' => $image->getId(),
			));

			return $this->addHtml($html)->setCallback('insertProductGalleryProduct')->success('The product was successfully updated.')->sendResponse();
		} else {
			return $this->error('There was a problem updating the product.', true)->sendResponse();
		}
	}

	/**
	 * Adds a product to a product section using a url
	 *
	 * @Route("/rest/products/add-product-to-section/{id}", name="thread_products_rest_add_product_to_section")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function addProductToSectionAction(Request $request, SectionProductGallery $section, $repository = 'StemsBlogBundle:Section')
	{
		// Get the url from the query parameter and attempt to parse the product
		$em = $this->getDoctrine()->getManager();
		$link = $em->getRepository($repository)->findOneBy(['entity' => $section->getId(), 'type' => 'product']);

		try {
			$product = $this->get('threadandmirror.products.service.product')->getProductFromUrl($request->get('url'));

			// Update the new section with the new product data
			$section->updateFromProduct($product);
			$em->persist($section);
			$em->flush();

			// Rebuild the form to get new values for render
			$form = $this->createForm(new SectionProductType($link), $section);

			// Render the section form and preview html with the valid values
			$formHtml = $this->renderView('ThreadAndMirrorProductsBundle:Section:productHiddenForm.html.twig', [
				'form' => $form->createView()
			]);

			$previewHtml = $this->renderView('ThreadAndMirrorProductsBundle:Section:productPreview.html.twig', [
				'section' => $section
			]);

			// Set the meta data for the update callback
			$meta = [
				'type'        => 'product',
				'section'     => $section->getId(),
				'formHtml'    => $formHtml,
				'previewHtml' => $previewHtml
			];

			return $this->addMeta($meta)->setCallback('updateSectionForm')->success('Image updated.')->sendResponse();

		} catch (ProductParseException $e) {
			return $this->error($e->getMessage(), true)->sendResponse();
		}
	}

	/**
	 * Adds a product to a product section using a url
	 *
	 * @Route("/rest/products/add-product-to-gallery-section/{id}", name="thread_products_rest_add_product_to_section")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function addProductToGallerySectionAction(Request $request, SectionProductGallery $section, $repository = 'StemsBlogBundle:Section')
	{
		// Get the url from the query parameter and attempt to parse the product
		$em   = $this->getDoctrine()->getManager();
		$link = $em->getRepository($repository)->findOneBy(['entity' => $section->getId(), 'type' => 'product']);

		try {
			$product = $this->get('threadandmirror.products.service.product')->getProductFromUrl($request->get('url'));

			$item = new SectionProductGalleryProduct($product);

			// Override the url with our internal version
			$item->setUrl($this->generateUrl('thread_products_front_product_buy', ['slug' => $product->getslug()]));

			// Add the item to the gallery
			$section->addProduct($item);
			$item->setSectionProductGallery($section);

			$em->persist($section);
			$em->persist($item);
			$em->flush();

			// Rebuild the form to get new values for render
			$form = $this->createForm(new SectionProductGalleryType($link), $section);

			// Render the section form and preview html with the valid values
			$formHtml = $this->renderView('ThreadAndMirrorProductsBundle:Section:productGalleryHiddenForm.html.twig', [
				'form' => $form->createView()
			]);

			$previewHtml = $this->renderView('ThreadAndMirrorProductsBundle:Section:productGalleryPreview.html.twig', [
				'link'    => $link,
				'form'    => $form->createView(),
				'section' => $section
			]);


			// Set the meta data for the update callback
			$meta = [
				'type'        => 'product_gallery',
				'section'     => $section->getId(),
				'formHtml'    => $formHtml,
				'previewHtml' => $previewHtml
			];

			return $this->addMeta($meta)->setCallback('updateSectionForm')->success('Product added.')->sendResponse();

		} catch (ProductParseException $e) {
			return $this->error($e->getMessage(), true)->sendResponse();
		}
	}
}
