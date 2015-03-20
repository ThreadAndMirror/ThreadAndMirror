<?php

namespace ThreadAndMirror\ProductsBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

/**
 *	Handles requested and stored filter paramters for the product search pages
 */

class ProductFilter
{
	// the entity manager
	protected $em;

	// selected shops to filter
	protected $shops = array();

	// all available shops to filter by
	protected $availableShops = array();

	// boolean to ignore shop filtering if all available shops are selected
	protected $ignoreShops = true;

	// filter name by keyword
	protected $keywords = null;

	// a limit override for the results 
	protected $limit;

	// selected categories to filter
	protected $categories = array();

	// all available categories to filter by
	protected $availableCategories = array(
		'Clothing'		=> 1,
		'Shoes'			=> 2,
		'Bags'			=> 3,
		'Accessories' 	=> 4,
		'Jewellery' 	=> 5,
	);

	// boolean to ignore category filtering if all available categories are selected
	protected $ignoreCategories = true;

	public function __construct(EntityManager $em)
	{
		// define all available options from entities
		$this->availableShops = $em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findAll();
	}

	// process any requested and stored filters
	public function process($request, $limit=null)
	{
		$this->limit = $limit;

		// get stored filters from the session, otherwise set default as a new filter
		if ($request->getSession()->get('productFilters')) {
			$this->loadFilters($request);
		} else {
			$this->createFilters();
		}

		// handle any posted changes to selections
		if ($request->getMethod() == 'POST') {
			$this->processKeywords($request);
			$this->updateShopFilters($request);
			$this->updateCategoryFilters($request);
		}

		// store the new/updated filter in the session
		$request->getSession()->set('productFilters', serialize($this));

		return $this;
	}

	// set up defaults for a new filter
	protected function createFilters()
	{
		// select all shops for filtering
		$this->shops = $this->availableShops;
		$this->categories = $this->availableCategories;

	}

	/**
	 * update the filtered categories based on the request
	 */
	protected function loadFilters($request)
	{
		// build the current filter object from the session
		$current = unserialize($request->getSession()->get('productFilters'));

		// get the current selections
		$this->shops = $current->getShops();
		$this->categories = $current->getCategories();
		$this->keywords = $current->getKeywords();
	}

	/**
	 * update the filtered shops based on the request
	 */
	protected function updateShopFilters(Request $request)
	{
		$requestedShops = array();

		// get a list of requested shops
		foreach ($request->request->all() as $name => $value) {
			if (stristr($name, 'filter-shop-')) {
				$requestedShops[] = str_replace('filter-shop-', '', $name);
			}
		}

		// update the shop filters based on the request	
		if ($requestedShops) {
			// reset the shop list
			$this->shops = array();
			// matched the requested shops vs. those available and save if there's a match
			foreach ($this->availableShops as $shop) {
				if (in_array($shop->getSlug(), $requestedShops)) {
					$this->shops[] = $shop;
				}
			}
		} else {
			// if no shops were ticked then select them all
			$this->shops = $this->availableShops;
		}

		// if the selected shops are less than all available shops then enable shop filtering
		count($this->shops) != count($this->availableShops) and $this->ignoreShops = false;
	}

	/**
	 * update the filtered categories based on the request
	 */
	protected function updateCategoryFilters(Request $request)
	{
		$requestedCategories = array();

		// get a list of requested categories
		foreach ($request->request->all() as $name => $value) {
			if (stristr($name, 'filter-category-')) {
				$requestedCategories[] = ucwords(str_replace('filter-category-', '', $name));
			}
		}

		// update the category filters based on the request	
		if ($requestedCategories) {
			// reset the category list
			$this->categories = array();
			// matched the requested categorys vs. those available and save if there's a match
			foreach ($this->availableCategories as $categoryName => $categoryId) {
				if (in_array($categoryName, $requestedCategories)) {
					$this->categories[$categoryName] = $categoryId;		
				}
			}

			// temporarily, add products without a category if clothing was requested
			if (array_key_exists('Clothing', $this->categories)) {
				$this->categories['Uncategorised'] = 0;
			}

		} else {
			// if no categories were ticked then select them all
			$this->categories = $this->availableCategories;
		}

		// if the selected categories are less than all available categories then enable category filtering
		count($this->categories) != count($this->availableCategories) and $this->ignoreCategories = false;
	}

	/**
	 * Clean the requested keywords string and store it in the fitler
	 *
	 * @param  Request 		$request
	 */
	public function processKeywords(Request $request) 
	{
		// Get the keyword string from the request
		$keywords = $request->request->get('filter-keywords');

		// Update the keyword string regardless (to clear when empty)
		$this->keywords = $keywords;
	}

	/**
	 * Builds a no result message based on requested filters
	 */
	public function getNoResultMessage()
	{
		$message = 'We\'re sorry, but your search ';

		if ($this->keywords !== null) {
			$message .= 'for "'.$this->keywords.'" ';
		}

		$message .= 'returned no results.';

		return $message;
	}

	public function getCategories()
	{
		return $this->categories;
	}

	public function getAvailableCategories()
	{
		return $this->availableCategories;
	}

	public function getIgnoreCategories()
	{
		return $this->categories ? false : true;
	}

	public function getShops()
	{
		return $this->shops;
	}

	public function getAvailableShops()
	{
		return $this->availableShops;
	}

	public function getIgnoreShops()
	{
		return $this->shops ? false : true;
	}

	public function getKeywords()
	{
		return $this->keywords;
	}

	public function getIgnoreKeywords()
	{
		return $this->keywords ? false : true;
	}

	public function getLimit()
	{
		return $this->limit;
	}
}
