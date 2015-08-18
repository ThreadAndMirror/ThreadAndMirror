<?php

namespace ThreadAndMirror\EditorsPicksBundle\Controller;

use Stems\CoreBundle\Controller\BaseRestController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ThreadAndMirror\EditorsPicksBundle\Entity\Collection;
use ThreadAndMirror\EditorsPicksBundle\Entity\Pick;
use ThreadAndMirror\ProductsBundle\Entity\Product;
use ThreadAndMirror\ProductsBundle\Entity\SectionProductGalleryProduct;
use ThreadAndMirror\EditorsPicksBundle\Form\CollectionType;

class PopupController extends BaseRestController
{
	/**
	 * Build a popup to manually add a product to a product gallery section, created a skeleton entity in the first place.
	 *
	 * @Route("/admin/popup/editors-picks/add-pick", name="thread_editorspicks_popup_collection_addpick")
	 */
	public function addPickToCollection(Request $request)
	{
		// Try to find the product based on the passed param
		$em      = $this->getDoctrine()->getManager();
		$offset  = $request->get('offset', 0);
		$product = $this->get('threadandmirror.products.service.product')->getProductFromUrl($request->get('url', null));

		// If the product couldn't be found then create a new one
		if ($product === null) {
			$product = new Product();
		}

		// Create the pick, collection and add placeholder picks so the correct form offset is generated
		$collection = new Collection();
		$pick 	    = new Pick($product);

		$collection->addPickAtOffset($pick, $offset);

		$form = $this->createForm(new CollectionType(), $collection);

		// Get the html for the pick editor form
		$html = $this->renderView('ThreadAndMirrorEditorsPicksBundle:Popup:form.html.twig', array(
			'form'	 => $form->createView(),
			'pick'	 => $pick,
			'offset' => $offset
		));

		return $this->addHtml($html)->success()->sendResponse();
	}
}