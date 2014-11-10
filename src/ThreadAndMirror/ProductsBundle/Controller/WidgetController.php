<?php

namespace ThreadAndMirror\ProductsBundle\Controller;

// Symfony Components
use Symfony\Bundle\FrameworkBundle\Controller\Controller,
	Symfony\Component\Security\Core\SecurityContext,
	Symfony\Component\HttpFoundation\RedirectResponse,
	Symfony\Component\HttpFoundation\Response,
	Symfony\Component\HttpFoundation\Request;

class WidgetController extends Controller
{
	/**
	 * Renders x amount the latest sale items, defaulting to $limit
	 */
	public function latestAdditionsAction($limit=6)
	{
		$em = $this->getDoctrine()->getManager();
		$request = $this->getRequest();

		// load the users filters if they're logged in
		$filters = $this->get('threadandmirror.product.filter')->process($request, $limit);

		// get the filtered products
		$products = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->getLatestAdditions($filters);

		return $this->render('ThreadAndMirrorProductsBundle:Widget:latestAdditions.html.twig', array(
			'products' 		=> $products,
		));
	}

	/**
	 * Renders a block with the latest featured outfit (ie. outfit of the week)
	 */
	public function latestFeaturedOutfitAction()
	{
		// get the latest featured outfit
		$em = $this->getDoctrine()->getEntityManager();
		$outfits = $em->getRepository('ThreadAndMirrorProductsBundle:Outfit')->findBy(array('deleted' => false), array('featured' => 'DESC'), 1);
		$outfit = reset($outfits);

		return $this->render('ThreadAndMirrorProductsBundle:Widget:latestFeaturedOutfit.html.twig', array(
			'outfit' 	=> $outfit,
		));
	}

	/**
	 * Renders the html for the affiliate feature (perhaps have this randomise in future for more than one feature?)
	 */
	public function affiliateFeatureAction()
	{
		return $this->render('ThreadAndMirrorProductsBundle:Widget:affiliateFeature.html.twig', array());
	}

	/**
	 * Renders an array of pids into products
	 */
	public function productListAction($pids)
	{
		$em = $this->getDoctrine()->getManager();
		$products = array();
		
		foreach ($pids as $pid) {
			$products[] = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->find($pid);
		}		

		return $this->render('ThreadAndMirrorProductsBundle:Widget:productList.html.twig', array(
			'products' 		=> $products,
		));
	}
}
