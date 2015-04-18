<?php

namespace ThreadAndMirror\ProductsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stems\CoreBundle\Controller\BaseFrontController;
use ThreadAndMirror\ProductsBundle\Entity\Brand;

class FrontController extends BaseFrontController
{
	/**
	 * Embeds the contextual main menu on the site
	 *
	 * @Template()
	 */
	public function menuAction($page)
	{
		return array(
			'page'	=> $page,
		);
	}

	/**
	 * Holding page
	 *
	 * @Route("/coming-soon", name="thread_products_front_product_holding")
	 * @Template()
	 */
	public function holdingAction()
	{
		$feed = $this->em->getRepository('StemsSocialBundle:InstagramFeed')->find(1);

		return array(
			'images' => $feed->getImages()
		);
	}

	/**
	 * Display a single product
	 *
	 * @Route("/product/{slug}", name="thread_products_front_product_view")
	 * @Template()
	 */
	public function productAction($slug)
	{
		// Get the id from the slug
		$id = explode('-', $slug);
		$id = end($id);

		// Get the product
		$product = $this->em->getRepository('ThreadAndMirrorProductsBundle:Product')->find($id);

		// Temporary 301s setup whilst google/users access old url format
		if (!stristr($slug, '-')) {
			return $this->redirect('/product/'.$product->getSlug(), 301);
		}

		// If the product hasn't been fully parsed or was last checked over 60 minutes ago then force an update
		$refresh = new \DateTime();
		$refresh->modify('-60 minutes');

		if ($product->getChecked() == null || $refresh > $product->getChecked()) {
			if ($product->getShop()->getHasCrawler()) {
				$this->get($product->getShop()->getUpdaterName())->updateProductFromCrawl($product);
				$this->em->persist($product);
				$this->em->flush();
			} else {
				// $product = $this->em->getRepository('ThreadAndMirrorProductsBundle:Product')->forceUpdate($product);
			}
		} 

		// Load the page object from the CMS
		$this->loadPage('product/{id}', array(
			'title' 			=> 'Product View',
			'windowTitle' 		=> $product->getName(),
			'metaKeywords' 		=> $product->getName().', '.$product->getShop()->getName(),
			'metaDescription' 	=> $product->getRawDescription(),
		));

		return array(
			'product' => $product,
			'page'	  => $this->page,
		);
	}

	/**
	 * Redirect to the product's affiliate link, or to their store link as a fallback
	 *
	 * @Route("/product/buy/{slug}", name="thread_products_front_product_buy")
	 */
	public function redirectBuyFromStoreAction($slug)
	{
		// Get the id from the slug
		$id = explode('-', $slug);
		$id = end($id);

		// Get the product
		$product = $this->em->getRepository('ThreadAndMirrorProductsBundle:Product')->find($id);

		return $this->redirect($product->getFrontendUrl());
	}

	/**
	 * Lists new fashion products based on the requested filters
	 *
 	 * @Route("/{area}/new-in", name="thread_products_front_new_in")
	 * @Template()
	 */
	public function newInAction(Request $request, $area)
	{
		// Process requested filters
		$filters = $this->get('threadandmirror.product.filter')->process($request);

		// Execute the elasticsearch query
		// $es          = $this->get('fos_elastica.manager');
		// $currentPage = 1;
    	// $products    = $es->getRepository('ThreadAndMirrorProductsBundle:Product')->findNewIn($filters, $area, $currentPage);
		// $query = new \Elastica\Query\QueryString($filters->getKeywords());
		// $term = new \Elastica\Filter\Term(array('area' => $area));

		// $filteredQuery = new \Elastica\Query\Filtered($query, $term);
		// $products = $this->get('fos_elastica.finder.search.product')->findPaginated($filteredQuery);

		// Get and process requested filters
		$filters = $this->get('threadandmirror.product.filter')->process($request);

		// Get the filtered products
		$products = $this->em->getRepository('ThreadAndMirrorProductsBundle:Product')->findNewIn($filters, $area);

		// Paginate the result
		$products = $this->get('stems.core.pagination')->paginate($products, $request, array('maxPerPage' => 100));

    	// Load the page object from the CMS
		$this->loadPage('{area}/new-in', array(
			'title'       => ucwords($area).' New In',
			'windowTitle' => ucwords($area).' New In',
		));

		// Load the page object from the CMS
		return array(
			'filters' 	=> $filters,
			'products' 	=> $products,
			'page'		=> $this->page,
		);
	}

	/**
	 * Lists all stores that have products in the specified area
	 *
	 * @Route("/{area}/stores", name="thread_products_front_stores")
	 * @Template()
	 */
	public function storesAction(Request $request, $area)
	{
		// Get the stores
		if ($area === 'fashion') {
			$stores = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findBy(array('hasFashion' => true), array('name' => 'ASC'));
		} else {
			$stores = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findBy(array('hasBeauty' => true), array('name' => 'ASC'));
		}

		// Load the page object from the CMS
		$this->loadPage('{area}/stores', array(
			'title'       => ucwords($area).' Stores',
			'windowTitle' => ucwords($area).' Stores',
		));

		return array(
			'area'		=> $area,
			'stores' 	=> $stores,
			'page'		=> $this->page,
		);
	}

	/**
	 * Show all products for a specific store in a specific area
	 *
	 * @Route("/{area}/stores/{slug}/products", name="thread_products_front_stores_products")
	 * @Template()
	 */
	public function storeAction(Request $request, $slug, $area)
	{
		// Get the products
		$shop     = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findOneBySlug($slug);
		$products = $this->em->getRepository('ThreadAndMirrorProductsBundle:Product')->findBy(array('shop' => $shop, 'area' => $area), array('added' => 'DESC'), 3000);

		// Paginate the result
		$data = $this->get('stems.core.pagination')->paginate($products, $request, array('maxPerPage' => 100));

		// Load the page object from the CMS
		$this->loadPage('{area}/stores/{slug}/products', array(
			'title' 	  => ucfirst($area).' at '.$shop->getName(),
			'windowTitle' => ucfirst($area).' at '.$shop->getName(),
		));

		// Load the page object from the CMS
		return array(
			'products' 	=> $data,
			'page'		=> $this->page,
			'shop'		=> $shop,
		);
	}

	/**
	 * Forward to the homepage of a given shop, using the affiliate link if possible
	 *
	 * @Route("/stores/{slug}/website", name="thread_products_front_store_website")
	 */
	public function storeWebsiteAction(Request $request, $slug)
	{
		// Get the stores
		$shop = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findOneBySlug($slug);

		// Redirect to their site
		return $this->redirect($shop->getFrontendUrl());
	}

	/**
	 * Lists all brands that have products in the specified area
	 *
	 * @Route("/{area}/brands", name="thread_products_front_brands")
	 * @Template()
	 */
	public function brandsAction(Request $request, $area)
	{
		// Get the brands
		if ($area === 'fashion') {
			$brands = $this->em->getRepository('ThreadAndMirrorProductsBundle:Brand')->findBy(array('hasFashion' => true), array('name' => 'ASC'));
		} else {
			$brands = $this->em->getRepository('ThreadAndMirrorProductsBundle:Brand')->findBy(array('hasBeauty' => true), array('name' => 'ASC'));
		}

		// Load the page object from the CMS
		$this->loadPage('{area}/brands', array(
			'title'       => ucwords($area).' Brands',
			'windowTitle' => ucwords($area).' Brands',
		));

		return array(
			'area'		=> $area,
			'brands' 	=> $brands,
			'page'		=> $this->page,
		);
	}

	/**
	 * Show all products for a specific brand in a specific area
	 *
	 * @Route("/{area}/brands/{slug}/products", name="thread_products_front_brands_products")
	 * @Template()
	 */
	public function brandAction(Request $request, Brand $brand, $area)
	{
		// Get the products
		$products = $this->em->getRepository('ThreadAndMirrorProductsBundle:Product')->findBy(array('brand' => $brand, 'area' => $area), array('added' => 'DESC'), 3000);

		// Paginate the result
		$data = $this->get('stems.core.pagination')->paginate($products, $request, array('maxPerPage' => 100));

		// Load the page object from the CMS
		$this->loadPage('{area}/brands/{slug}/products', array(
			'title' 	  => ucfirst($area).' by '.$brand->getName(),
			'windowTitle' => ucfirst($area).' by '.$brand->getName(),
		));

		return array(
			'products' 	=> $data,
			'page'		=> $this->page,
			'brand'		=> $brand,
		);
	}

	/**
	 * Show all products for a specific brand and category
	 *
	 * @Route("/brands/{slug}/{category}", name="thread_products_front_brands_category")
	 * @Template()
	 */
	public function brandCategoryAction(Request $request, Brand $brand, $category)
	{
		// Get the product
		$category = $this->em->getRepository('ThreadAndMirrorProductsBundle:Category')->findOneBy(array('slug' => $category));
		$products = $this->em->getRepository('ThreadAndMirrorProductsBundle:Product')->findBy(array('brand' => $brand, 'category' => $category), array('added' => 'DESC'), 3000);

		// Paginate the result
		$data = $this->get('stems.core.pagination')->paginate($products, $request, array('maxPerPage' => 100));

		// Load the page object from the CMS
		$this->loadPage('brands/{slug}/{category}', array(
			'title' 	  => $brand->getName().' '.$category->getName(),
			'windowTitle' => $brand->getName().' '.$category->getName(),
		));

		return array(
			'products' 	=> $data,
			'page'		=> $this->page,
			'brand'		=> $brand,
			'category'	=> $category,
		);
	}

	/**
	 * Lists all products from the specified area
	 *
	 * @Route("/{area}/products", name="thread_products_front_products")
	 * @Template()
	 */
	public function productsAction(Request $request, $area)
	{
		// Get the stores
		if ($area === 'fashion') {
			$stores = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findBy(array('hasFashion' => true), array('name' => 'ASC'));
		} else {
			$stores = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findBy(array('hasBeauty' => true), array('name' => 'ASC'));
		}

		// Load the page object from the CMS
		$this->loadPage('{area}/products', array(
			'title'       => ucwords($area),
			'windowTitle' => ucwords($area),
		));

		return array(
			'stores' => $stores,
			'page'	 => $this->page,
		);
	}

	/**
	 * Lists all sale products from the specified area
	 *
	 * @Route("/{area}/sale", name="thread_products_front_sale")
	 * @Template()
	 */
	public function saleAction(Request $request, $area)
	{
		// Get and process requested filters
		$filters = $this->get('threadandmirror.product.filter')->process($request);

		// Get the filtered products
		$products = $this->em->getRepository('ThreadAndMirrorProductsBundle:Product')->findSale($filters, $area);

		// Paginate the result
		$data = $this->get('stems.core.pagination')->paginate($products, $request, array('maxPerPage' => 100));

		// Load the page object from the CMS
		$this->loadPage('{area}/sale', array(
			'title' 	  => ucfirst($area).' Sale Products',
			'windowTitle' => ucfirst($area).' Sale Products',
		));

		return array(
			'filters'	=> $filters,
			'products' 	=> $data,
			'page'		=> $this->page,
		);
	}

	/**
	 * Display the user's wishlist
	 *
	 * @Route("/wishlist", name="thread_products_front_wishlist")
	 * @Template()
	 */
	public function wishlistAction(Request $request)
	{
		// Get the user's monitored wishlist
		$wishlist = $this->em->getRepository('ThreadAndMirrorProductsBundle:Wishlist')->findOneByOwner($this->getUser()->getId());

		// Get the user's outfits for the adder menu
		$outfits = $this->em->getRepository('ThreadAndMirrorProductsBundle:Outfit')->findBy(array('owner' => $this->getUser()->getId(), 'deleted' => false));

		// Paginate the result 
		$data = $this->get('stems.core.pagination')->paginate($wishlist->getPicks()->toArray(), $request, array('maxPerPage' => 100));

		// Load the page object from the CMS
		$this->loadPage('wishlist', array(
			'title' 	=> $this->getUser()->getFullname().'\'s Wishlist',
		));

		return array(
			'outfits'	=> $outfits,
			'picks'		=> $data,
			'page'		=> $this->page,
		);
	}

	/**
	 * Display the user's outfits
	 *
	 * @Route("/outfits", name="thread_products_front_outfits")
	 * @Template()
	 */
	public function outfitsAction(Request $request)
	{
		// get the user's outfits
		$outfits = $this->em->getRepository('ThreadAndMirrorProductsBundle:Outfit')->findBy(array('owner' => $this->getUser()->getId(), 'deleted' => false));

		// paginate the result
		$data = $this->get('stems.core.pagination')->paginate($outfits, $request, array('maxPerPage' => 18));

		// Load the page object from the CMS
		$this->loadPage('outfits', array(
			'title' 	=> $this->getUser()->getFullname().'\'s Outfits',
		));

		return array(
			'outfits' => $data,
			'page'	  => $this->page,
		);
	}
}
