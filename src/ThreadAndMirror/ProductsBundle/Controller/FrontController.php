<?php

namespace ThreadAndMirror\ProductsBundle\Controller;

// Symfony Components
use Symfony\Bundle\FrameworkBundle\Controller\Controller,
	Symfony\Component\Security\Core\SecurityContext,
	Symfony\Component\HttpFoundation\RedirectResponse,
	Symfony\Component\HttpFoundation\Response,
	Symfony\Component\HttpFoundation\Request;

class FrontController extends Controller
{
	/**
	 * Embeds the contextual main menu on the site
	 *
	 * @param $page Page The page entity for the view requesting the menu
	 */
	public function menuAction($page)
	{
		$em = $this->getDoctrine()->getManager();

		// manually grab the pages we want for the main menu
		$items = array(
			$em->getRepository('StemsPageBundle:Page')->findOneBySlug('latest-additions'),
			$em->getRepository('StemsPageBundle:Page')->findOneBySlug('wishlist'),
			$em->getRepository('StemsPageBundle:Page')->findOneBySlug('blog'),
			$em->getRepository('StemsPageBundle:Page')->findOneBySlug('street-chic'),
			$em->getRepository('StemsPageBundle:Page')->findOneBySlug('style-exchange'),
		);

		return $this->render('ThreadAndMirrorProductsBundle:Front:menu.html.twig', array(
			'items' 		=> $items,
			'page'			=> $page,
		));
	}

	/**
	 * Display a single product
	 *
	 * @param  string 		$slug  	The slug of the product
	 */
	public function productAction($slug)
	{
		// Get the id from the slug
		$id = explode('-', $slug);
		$id = end($id);

		// Get the product
		$em = $this->getDoctrine()->getManager();
		$product = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->find($id);

		// Escape if it not longer exists
		if (!$product) {
			return $this->redirect('/latest-additions');
		}

		// Temporary 301s setup whilst google/users access old url format
		if (!stristr($slug, '-')) {
			return $this->redirect('/product/'.$product->getSlug(), 301);
		}

		// If the product hasn't been fully parsed yet then do it before displaying (turn back on when the product doesn't error)
		$refresh = new \DateTime();
		$refresh->modify('-15 minutes');

		// If the product hasn't been fully parsed or was last checked over 15 minutes ago then force an update
		if (!$product->getFullyParsed() || $refresh > $product->getChecked()) {
			$product = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->forceUpdate($product);
		} 

		// Load the page object from the CMS
		$page = $em->getRepository('StemsPageBundle:Page')->load('product/{id}', array(
			'title' 			=> 'Product View',
			'windowTitle' 		=> $product->getName(),
			'metaKeywords' 		=> $product->getName().', '.$product->getShop()->getName(),
			'metaDescription' 	=> $product->getRawDescription(),
		));

		return $this->render('ThreadAndMirrorProductsBundle:Front:product.html.twig', array(
			'product' 		=> $product,
			'page'			=> $page,
		));
	}

	/**
	 * Redirect to the product's affiliate link, or to their store link as a fallback
	 *
	 * @param  string 		$slug  	The slug of the product
	 */
	public function redirectBuyFromStoreAction($slug)
	{
		// Get the id from the slug
		$id = explode('-', $slug);
		$id = end($id);

		// Get the product
		$em = $this->getDoctrine()->getManager();
		$product = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->find($id);

		// Escape if it not longer exists
		if (!$product) {
			return $this->redirect('/latest-additions');
		}

		return $this->redirect($product->getFrontendUrl());
	}

	/**
	 * Lists all fashion products
	 *
	 * @param  Request 	$request
	 */
	public function fashionAction(Request $request)
	{
		// load the page object from the CMS
		$em = $this->getDoctrine()->getManager();
		$page = $em->getRepository('StemsPageBundle:Page')->load('fashion');

		// get and process requested filters
		$filters = $this->get('threadandmirror.product.filter')->process($request);

		// get the filtered products
		$products = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->getAllFashion($filters);

		// paginate the result
		$data = $this->get('stems.core.pagination')->paginate($products, $request, array('maxPerPage' => 100));

		return $this->render('ThreadAndMirrorProductsBundle:Front:fashion.html.twig', array(
			'filters'		=> $filters,
			'products' 		=> $data,
			'page'			=> $page,
		));
	}

	/**
	 * Lists all stores that have fashion products
	 * @param $request Request
	 */
	public function fashionStoresAction(Request $request)
	{
		// load the page object from the CMS
		$em = $this->getDoctrine()->getManager();
		$page = $em->getRepository('StemsPageBundle:Page')->load('fashion/stores');

		// get the filtered products
		$stores = $em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findBy(array('attire' => true, 'deleted' => false), array('name' => 'ASC'));

		return $this->render('ThreadAndMirrorProductsBundle:Front:stores.html.twig', array(
			'stores' 		=> $stores,
			'page'			=> $page,
		));
	}

	/**
	 * Lists new fashion products based on the requested filters
	 * @param $request Request
	 */
	public function latestAdditionsAction(Request $request)
	{
		// load the page object from the CMS
		$em = $this->getDoctrine()->getManager();
		$page = $em->getRepository('StemsPageBundle:Page')->load('latest-additions');

		// get and process requested filters
		$filters = $this->get('threadandmirror.product.filter')->process($request);

		// get the filtered products
		$products = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->getLatestAdditions($filters);

		// paginate the result
		$data = $this->get('stems.core.pagination')->paginate($products, $request, array('maxPerPage' => 100));

		return $this->render('ThreadAndMirrorProductsBundle:Front:newIn.html.twig', array(
			'filters'		=> $filters,
			'products' 		=> $data,
			'page'			=> $page,
		));
	}

	/**
	 * Lists the fashion sale products based on the requested filters
	 * @param $request Request
	 */
	public function saleItemsAction(Request $request)
	{
		// load the page object from the CMS
		$em = $this->getDoctrine()->getManager();
		$page = $em->getRepository('StemsPageBundle:Page')->load('sale-items');

		// get and process requested filters
		$filters = $this->get('threadandmirror.product.filter')->process($request);

		// get the filtered products
		$products = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->getSaleItems($filters);

		// paginate the result
		$data = $this->get('stems.core.pagination')->paginate($products, $request, array('maxPerPage' => 100));

		return $this->render('ThreadAndMirrorProductsBundle:Front:saleItems.html.twig', array(
			'filters'		=> $filters,
			'products' 		=> $data,
			'page'			=> $page,
		));
	}

	/**
	 * Lists all stores that have beauty products
	 * @param $request Request
	 */
	public function beautyStoresAction(Request $request)
	{
		// load the page object from the CMS
		$em = $this->getDoctrine()->getManager();
		$page = $em->getRepository('StemsPageBundle:Page')->load('beauty/stores');

		// get the filtered products
		$stores = $em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findBy(array('beauty' => true, 'deleted' => false));

		return $this->render('ThreadAndMirrorProductsBundle:Front:stores.html.twig', array(
			'stores' 		=> $stores,
			'page'			=> $page,
		));
	}

	/**
	 * Lists the latest beauty products based on the requested filters
	 * @param $request Request
	 */
	public function beautyNewInAction(Request $request)
	{
		// load the page object from the CMS
		$em = $this->getDoctrine()->getManager();
		$page = $em->getRepository('StemsPageBundle:Page')->load('beauty/new-in');

		// get and process requested filters
		$filters = $this->get('threadandmirror.product.filter')->process($request);

		// get the filtered products
		$products = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->getBeauty($filters, 'new');

		// paginate the result
		$data = $this->get('stems.core.pagination')->paginate($products, $request, array('maxPerPage' => 100));

		return $this->render('ThreadAndMirrorProductsBundle:Front:beautyNewIn.html.twig', array(
			'filters'		=> $filters,
			'products' 		=> $data,
			'page'			=> $page,
		));
	}

	/**
	 * Lists the latest sale beauty products based on the requested filters
	 * @param $request Request
	 */
	public function beautySaleAction(Request $request)
	{
		// load the page object from the CMS
		$em = $this->getDoctrine()->getManager();
		$page = $em->getRepository('StemsPageBundle:Page')->load('beauty/sale');

		// get and process requested filters
		$filters = $this->get('threadandmirror.product.filter')->process($request);

		// get the filtered products
		$products = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->getBeauty($filters, 'sale');

		// paginate the result
		$data = $this->get('stems.core.pagination')->paginate($products, $request, array('maxPerPage' => 100));

		return $this->render('ThreadAndMirrorProductsBundle:Front:beautyNewIn.html.twig', array(
			'filters'		=> $filters,
			'products' 		=> $data,
			'page'			=> $page,
		));
	}


	/**
	 * Show all products for a specific store
	 * @param $request Request
	 */
	public function storeAction($slug, Request $request)
	{
		// load the page object from the CMS
		$em = $this->getDoctrine()->getManager();
		$page = $em->getRepository('StemsPageBundle:Page')->load('stores/{slug}');

		// get the filtered products
		$filters = array();
		$shop = $em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findOneBySlug($slug);
		$products = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->findBy(array('shop' => $shop), array('added' => 'DESC'), 3000);

		// paginate the result
		$data = $this->get('stems.core.pagination')->paginate($products, $request, array('maxPerPage' => 100));

		// load the page object from the CMS
		$page = $em->getRepository('StemsPageBundle:Page')->load('product/{id}', array(
			'title' 			=> 'Products at '.$shop->getName(),
			'windowTitle' 		=> 'Products at '.$shop->getName(),
		));

		return $this->render('ThreadAndMirrorProductsBundle:Front:store.html.twig', array(
			'products' 		=> $data,
			'page'			=> $page,
			'shop'			=> $shop,
		));
	}

	/**
	 * Display the user's wishlist
	 * @param $request Request
	 */
	public function wishlistAction(Request $request)
	{
		// load the page object from the CMS
		$em = $this->getDoctrine()->getManager();
		$page = $em->getRepository('StemsPageBundle:Page')->load('wishlist', array(
			'title' 	=> $this->getUser()->getFullname().'\'s Wishlist',
		));

		// get the user's monitored wishlist
		$wishlist = $em->getRepository('ThreadAndMirrorProductsBundle:Wishlist')->findOneByOwner($this->getUser()->getId());

		// get the user's outfits for the adder menu
		$outfits = $em->getRepository('ThreadAndMirrorProductsBundle:Outfit')->findBy(array('owner' => $this->getUser()->getId(), 'deleted' => false));

		// paginate the result 
		$data = $this->get('stems.core.pagination')->paginate($wishlist->getPicks()->toArray(), $request, array('maxPerPage' => 100));

		return $this->render('ThreadAndMirrorProductsBundle:Front:wishlist.html.twig', array(
			'outfits'	=> $outfits,
			'picks'		=> $data,
			'page'		=> $page,
		));
	}

	/**
	 * List all of the user's outfits
	 * @param $request Request
	 */
	public function outfitsAction(Request $request)
	{
		// load the page object from the CMS
		$em = $this->getDoctrine()->getManager();
		$page = $em->getRepository('StemsPageBundle:Page')->load('outfits', array(
			'title' 	=> $this->getUser()->getFullname().'\'s Outfits',
		));

		// get the user's outfits
		$outfits = $em->getRepository('ThreadAndMirrorProductsBundle:Outfit')->findBy(array('owner' => $this->getUser()->getId(), 'deleted' => false));

		// paginate the result
		$data = $this->get('stems.core.pagination')->paginate($outfits, $request, array('maxPerPage' => 18));

		return $this->render('ThreadAndMirrorProductsBundle:Front:outfits.html.twig', array(
			'outfits'	=> $data,
			'page'		=> $page,
		));
	}

	/**
	 * List the designers
	 */
	public function designersAction()
	{
		// get the designers
		$em = $this->getDoctrine()->getManager();
		$designers = $em->getRepository('ThreadAndMirrorProductsBundle:Designer')->findBy(array('deleted' => false), array('name' => 'DESC'));

		// load the page object from the CMS
		$page = $em->getRepository('StemsPageBundle:Page')->load('designers');

		return $this->render('ThreadAndMirrorProductsBundle:Front:designers.html.twig', array(
			'designers' 	=> $designers,
			'page'			=> $page,
		));
	}

	/**
	 * Display products for a designer
	 * @param $slug string   The slug of the designer
	 */
	public function designerAction($slug)
	{
		// get the designer
		$em = $this->getDoctrine()->getManager();
		$designer = $em->getRepository('ThreadAndMirrorProductsBundle:designer')->findOneBySlug($slug);

		// escape if it not longer exists
		if (!$designer) {
			return $this->redirect('/designers');
		}

		// find products belonging to the designer
		$products = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->findBy(array('designer' => $designer->getId()), array('added' => 'DESC'));

		// load the page object from the CMS
		$page = $em->getRepository('StemsPageBundle:Page')->load('designer/{name}', array(
			'title' 			=> $product->getName(),
			'windowTitle' 		=> $designer->getName(),
			'metaKeywords' 		=> $designer->getName().', '.implode(', ', $designer->getCategoryNames()),
			'metaDescription' 	=> $designer->getDescription(),
		));

		return $this->render('ThreadAndMirrorProductsBundle:Front:designer.html.twig', array(
			'products' 		=> $products,
			'page'			=> $page,
		));
	}

	/**
	 * Display a category of products for a designer
	 * @param $slug string   	The slug of the designer
	 * @param $category string  The slug of the category
	 */
	public function designerCategoryAction($slug, $category)
	{

		// clean the category from the slug


		// get the designer
		$em = $this->getDoctrine()->getManager();
		$designer = $em->getRepository('ThreadAndMirrorProductsBundle:designer')->findOneBySlug($slug);

		// escape if the designer no longer exists
		if (!$designer) {
			return $this->redirect('/designers');
		}

		// get the category

		// ....

		// go to the designers page if they don't have that category
		if (!$category) {
			return $this->redirect('/designer/'.$designer->getSlug());
		}

		// find products belonging to the designer
		$products = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->findBy(array('designer' => $designer->getId()), array('added' => 'DESC'));

		// load the page object from the CMS
		$page = $em->getRepository('StemsPageBundle:Page')->load('designer/{name}/{category}', array(
			'title' 			=> $product->getName(),
			'windowTitle' 		=> $designer->getName(),
			'metaKeywords' 		=> $designer->getName().', '.implode(', ', $designer->getCategoryNames()),
			'metaDescription' 	=> $designer->getDescription(),
		));

		return $this->render('ThreadAndMirrorProductsBundle:Front:designerCategory.html.twig', array(
			'products' 		=> $products,
			'page'			=> $page,
		));
	}
}
