<?php

namespace ThreadAndMirror\ProductsBundle\Parser;

use Symfony\Component\DomCrawler\Crawler,
	ThreadAndMirror\ProductsBundle\Entity\Product,
	ThreadAndMirror\AlertBundle\Entity\AlertBackInStock,
	ThreadAndMirror\AlertBundle\Entity\AlertNowOnSale,
	ThreadAndMirror\AlertBundle\Entity\AlertSizeInStock,
	ThreadAndMirror\AlertBundle\Entity\AlertFurtherPriceChange,
	ThreadAndMirror\ProductsBundle\Exception\ProductAlreadyParsedException;
	

class BaseParser
{
	
	/**
	 * The shop the parser belongs to
	 */
	protected $shop;

	/**
	 * The parser service, which includes other dependencies such as the entity manager
	 */
	protected $services;

	/**
	 * The url bein parsed, either manually entered or belonging to the product
	 */
	protected $url;

	/**
	 * The base url structure for sale list pages
	 */
	protected $urlSale = 'http://www.example.com/{category}/{page}';

	/**
	 * The base url structure for latest additions list pages
	 */
	protected $urlLatest = 'http://www.example.com/{category}/{page}';

	/**
	 * The timestamp for timed data & logs
	 */
	protected $timestamp;

	/**
	 * The existing product in the database, if there is one
	 */
	protected $existing;

	/**
	 * The new product, be it a fresh one or new details being parsed
	 */
	protected $product;

	/**
	 * The categories to iterate through for a list parse, defaulting to the single 'all' and storing null so the individual parsers work it out
	 */
	protected $categories = array('all' => null);

	/**
	 * Some shops categorise their sale pages differently, so use an alterntive list if there is one
	 */
	protected $categoriesSale = array();

	/**
	 * The pages to iterate through for the current category in a list parse
	 */
	protected $pages = 1;

	/**
	 * Multiplier for the page offset, should a shop use this for pagination
	 */
	protected $multiplier = 1;

	/**
	 * The css selector of each product item in a list view
	 */
	protected $selector;

	/**
	 * similar to categories, some shops have a different sale page structure
	 */
	protected $selectorSale;

	/**
	 * the type of list parsing being performed
	 */
	protected $mode;

	public function __construct($services)
	{
		$this->services = $services;
		$this->timestamp = new \DateTime();
	}

	/**
	 * Parse a new product from the given url
	 *
	 * @param  string 	$url 	The url of the product's page
	 * @return mixed 			Either the Product entity parsed or false if errors were encountered
	 */
	public function create($url)
	{
		$this->url = $url;
		$this->services->crawl($url);
		$this->product = new Product();

		// get the product details and return as failed if it can't be parsed
		if (!$this->getDetails()) {
			$this->services->getLogger()->error('Parsing product details failed for '.$this->url);
			// echo 'Parsing product details failed for '.$this->url;
			return false;
		}

		// check if the product already exists
		$shop = $this->services->getManager()->getRepository('ThreadAndMirrorProductsBundle:Shop')->getShopFromUrl($this->url);
		$this->existing = $this->services->getManager()->getRepository('ThreadAndMirrorProductsBundle:Product')->findOneBy(array('pid' => $this->product->getPid(),'shop' => $shop));

		// return the existing product and stop parsing
		if (is_object($this->existing)) {
			return $this->existing;
		} else {
			$this->product->setShop($shop);
		}

		// set the type of product we're handling
		$this->product->setAttire($shop->getHasFashion());
		$this->product->setBeauty($shop->getHasBeauty());

		// check if the product is in stock
		$this->getAvailability();

		// parse the sale prices first, otherwise fallback and parse the normal price
		if (!$this->getSalePrices()) {
			if (!$this->getPrices()) {
				$this->services->getLogger()->error('Parsing product prices failed for '.$this->url);
				// echo 'Parsing product prices failed for '.$this->url;
				return false;
			}
		}

		// get the affiliate link if we can
		$this->product->setAffiliateUrl($this->parseAffiliateLink($this->product, $url));

		// markup the description field
		$this->product->setDescription($this->markupDescription($this->product->getDescription()));

		// if all parsing was successful, tidy up the strings
		$this->product->setName($this->services->cleanupString($this->product->getName()));
		$this->product->setDescription($this->services->cleanupString($this->product->getDescription()));

		return $this->product;
	}

	/**
	 * Update the product from its url if any changes are found
	 *
	 * @param  Product 	$existing 	The existing product entity 
	 * @return Product 				Either the updated product, or the original product if no changes were made/errors were encountered
	 */
	public function update($existing, $force=false)
	{
		$this->existing = $existing;
		$this->product = clone $this->existing;

		// try to clean out the affilate links
		if (!$existing->getUrl()) {
			$existing->setUrl($existing->getAffiliateUrl());
		}

		// decode the affiliate links 15810
		if (stristr($existing->getUrl(), '&http:')) {
			$this->url = explode('&http:', $existing->getUrl());
			$this->url = 'http:'.end($this->url);
			$force = true;
		} else if (stristr($existing->getUrl(), 'click.linksynergy.com')) {
			$url = explode('RD_PARM1=', $existing->getUrl());
			$url = end($url);
			$this->url = rawurldecode(rawurldecode($url));
			$force = true;
		} else if (stristr($existing->getUrl(), 'http%')) {
			$this->url = rawurldecode($existing->getUrl());
			$force = true;
		} else {
			$this->url = $existing->getUrl();
		}
		


		$this->services->crawl($this->url);

		// check if the product has expired yet, before we try to parse it
		if ($this->getExpired()) {
			$this->services->getLogger()->warning('Product no longer exists for '.$this->url);
			// echo 'Product not longer exists for '.$this->url;
			$this->existing->setExpired($this->timestamp);
			return $this->existing;
		}

		// only parse the rest if the product is in stock, otherwise the crawler may break
		if ($this->getAvailability()) {

			// check the product details and return as failed if it can't be parsed
			if (!$this->getDetails()) {
				$this->services->getLogger()->error('Parsing product details failed for '.$this->url);

				// carry on if it's the first parse
				if ($this->existing->getFullyParsed()) {
					return $this->existing;
				}
				
			}

			// parse the sale prices first, otherwise fallback and parse the normal price.
			if (!$this->getSalePrices()) {
				if (!$this->getPrices()) {
					$this->services->getLogger()->error('Parsing product prices failed for '.$this->url);

					// carry on if it's the first parse
					if ($this->existing->getFullyParsed()) {
						return $this->existing;
					}
				}
			}

			// check the product details and return as failed if it can't be parsed
			if (!$this->getSizes()) {
				$this->services->getLogger()->error('Parsing product sizes failed for '.$this->url);

				// carry on if it's the first parse
				if ($this->existing->getFullyParsed()) {
					return $this->existing;
				}
				
			}

			// get the style recommendations
			if (!$this->product->getStyleWith()) {
				$this->getStyleWith();
			}
		} 

		// markup the description field
		if (!$this->existing->getFullyParsed()) {
			$this->existing->setDescription($this->markupDescription($this->existing->getDescription()));
		}

		// update the affiliate link if we can
		if ($this->existing->getShop()->getAffiliateId() && !$this->existing->getAffiliateUrl()) {
			$this->updateAffiliateLink($this->existing->getUrl());
		}

		// tidy up the product strings as a matter of course (temporary)
		$this->existing->setName($this->services->cleanupString($this->existing->getName()));
		$this->existing->setDescription($this->services->cleanupString($this->existing->getDescription()));

		// run the check for any changes, updating the existing product if there are
		return $this->checkChanges($force);
	}

	/**
	 * Parse the latest products and save any new ones
	 *
	 * @param  array 	$existing 	A list of exisiting product IDs
	 * @return int  				The count of new products that were found and added to the database
	 */
	public function latest($existing)
	{
		$results = 0;
		$this->mode = 'latest';

		// iterate through each category
		foreach ($this->categories as $categorySlug => $categoryId) {

			// crawl the first page so we can calculate how many pages are needed before iteration
			$this->services->crawl($this->buildUrl($this->urlLatest, $categorySlug, 1));

			// check how many pages need crawling
			$this->pages = $this->getPages();

			// iterate through all of the sale product pages on the site
			for ($i=1; $i <= $this->pages; $i++) {

				// gather the products from the DOM
				$products = $this->getProducts();

				// begin persisting the current page of products until we reach products that we've already covered
				try
				{
					foreach ($products as &$product) {
						// check whether the new product is already in the db, if not then add to shop and persist
						if (!in_array($product->getPid(), $existing)) {
							// sometimes the crawler will result in an empty entity, so don't persist
							if ($product->getName()) {
								$product->setShop($this->services->getManager()->getReference('ThreadAndMirrorProductsBundle:Shop', $this->shop));
								$product->setCategory($categoryId);
								$this->services->getManager()->persist($product);

								// add the new pid to the existing product array to prevent repeats
								$existing[] = $product->getPid();
								$results++;
							}	
						} else {
							// escape from looping the pages once we find an item that's already added (turned off for now);
							//throw new ProductAlreadyParsedException();
						}
					}
					
					$this->services->getManager()->flush();
				}
				catch (ProductAlreadyParsedException $e) 
				{
					// flush the final new products before killing the loop
					$this->services->getManager()->flush();
					break;
				}

				// get the next page of results if we're not on the last page
				if ($i < $this->pages) {
					$this->services->crawl($this->buildUrl($this->urlLatest, $categorySlug, (($i*$this->multiplier)+1)));
				} else {
					// escape the loop if we've processed the last page
					break;
				}
			}
		}

		return $results;
	}

	/**
	 * Parse the sale products, save any new ones, and update any current that are now on sale
	 *
	 * @param  array 	$existing 	A list of exisiting product IDs
	 * @return int  				The count of new products that were found and added to the database
	 */
	public function sale($existing)
	{
		$results = 0;
		$this->mode = 'sale';
		$this->shop = $this->services->getManager()->getRepository('ThreadAndMirrorProductsBundle:Shop')->find($this->shop);

		// switch to the sale category set if one has been defined
		if ($this->categoriesSale) {
			$categories = $this->categoriesSale;
		} else {
			$categories = $this->categories;
		}

		// iterate through each category
		foreach ($categories as $categorySlug => $categoryId) {

			// crawl the first page so we can calculate how many pages are needed before iteration
			$this->services->crawl($this->buildUrl($this->urlSale, $categorySlug, 1));

			// check how many pages need crawling
			$this->pages = $this->getPages();

			// iterate through all of the sale product pages on the site
			for ($i=1; $i <= $this->pages; $i++) {

				// gather the products from the DOM
				$products = $this->getProducts();

				// begin persisting the current page of products until we reach products that we've already covered
				try
				{
					foreach ($products as &$product) {
						// check whether the new product is already in the db, if not then add to shop and persist
						if (!in_array($product->getPid(), $existing)) {
							// sometimes the crawler will result in an empty entity, so don't persist
							if ($product->getName()) {
								$product->setShop($this->shop);
								$product->setCategory($categoryId);
								$this->services->getManager()->persist($product);
								
								// add the new pid to the existing product array to prevent repeats
								$existing[] = $product->getPid();
								$results++;
							}	
						} else {
							// if the product already exists, set it as sale if it isn't already
							$duplicate = $this->services->getManager()->getRepository('ThreadAndMirrorProductsBundle:Product')->findOneBy(array('shop' => $this->shop, 'pid' => $product->getPid()));
							
							if (is_object($duplicate) && !$duplicate->getSale()) {
								$duplicate->setSale(new \DateTime());
								$duplicate->setUpdated(new \DateTime());
								$this->services->getManager()->persist($duplicate);
								$results++;
							} else {
								// turn off the loop breaker for now
								// throw new ProductAlreadyParsedException();
							}
						}
					}
					
					$this->services->getManager()->flush();
				}
				catch (ProductAlreadyParsedException $e) 
				{
					// flush the final new products before killing the loop
					$this->services->getManager()->flush();
					break;
				}

				// get the next page of results if we're not on the last page
				if ($i < $this->pages) {
					$this->services->crawl($this->buildUrl($this->urlSale, $categorySlug, (($i*$this->multiplier)+1)));
				} else {
					// escape the loop if we've processed the last page
					break;
				}
			}
		}

		return $results;
	}

	/**
	 * Builds the full url string using the passed page number and category
	 *
	 * @param  string 	$url 		The template url 
	 * @param  string 	$category	The category slug 
	 * @param  integer 	$page 		The page number
	 * @return string  				The compiled url
	 */
	protected function buildUrl($url, $category, $page) 
	{
		$url = str_replace('{category}', $category, $url);
		$url = str_replace('{page}', $page, $url);

		return $url;
	}

	/**
	 * Default page getter to return 1 page, extended in the child parser if necessary
	 *
	 * @return integer 				The maximum amount of pages
	 */
	protected function getPages()
	{
		$this->pages = 1;

		return $this->pages;
	}

	/**
	 * Parses all products from the current crawler data using the parsers specified css selector
	 *
	 * @return array 				The Product entitities generated from the crawler data
	 */
	protected function getProducts()
	{
		// generate subcrawlers for each product detected, as the closure won't let us pass anything in!
		if ($this->mode == 'sale' && $this->selectorSale) {
			$selector = $this->selectorSale;
		} else {
			$selector = $this->selector;
		}

		$subcrawlers = $this->services->getCrawler()->filter($selector)->each(function ($node, $i) {
    
			// create a new crawler for the product html and create a new entity for it
			$subcrawler = new Crawler($node);
			
			return $subcrawler;
		});

		// parse the products
		$products = array();

		foreach ($subcrawlers as $subcrawler) {
			
			$product = new Product();

			// set product as new if parsing latest products
			if ($this->mode == 'latest') {
				$product->setNew(true);
			}

			// get the core product details
			if (!$this->getDetailsList($product, $subcrawler)) {
				// failed to get any details, so log
				$this->services->getLogger()->error('Parsing list product ('.$this->mode.') details failed for shop ID '.$this->shop);
			}

			// see if we can get the sale prices first
			$sale = $this->getSalePricesList($product, $subcrawler);

			// if there's no sale price or the was price comes through as 0 (ie. sale item in the wrong place) then get normal prices
			if (!$sale || $product->getWas() == 0) {
				if (!$this->getPricesList($product, $subcrawler)) {
					// if we couldn't get a price at all then there's a problem - log it and move on
					$this->services->getLogger()->error('Parsing list product ('.$this->mode.') prices failed for shop ID '.$this->shop);
				}
			}

			// tidy up the strings
			$product->setName($this->services->cleanupString($product->getName()));
			
			$products[] = $product;
		}

		// perform any special post processing (to handle things like netaporter's shit layouts)
		$method = $this->mode.'PostProcessing';
		$this->$method($products);

		return $products;
	}

	/**
	 * Decides whether an individually parsed product has significant changes since the last update (eg. now of sale) and creates any necessary alerts
	 *
	 * @param  boolean 	$force 		Whether to force an update regardless of the data
	 * @return Product  			The updated product if changes were made or the original of they weren't
	 */
	protected function checkChanges($force)
	{
		$changed = false;

		// update regardless if the request is forced
		$force and $changed = true;

		// check if this is the first individual parse of the product
		if (!$this->product->getFullyParsed()) {

			$changed = true;
			$this->existing->setFullyParsed(true);
		}

		// check whether the stock status has been changed
		if ($this->existing->getAvailable() != $this->product->getAvailable()) {

			$changed = true;	

			// create a back in stock alert if the product was out of stock previously
			if ($this->product->getAvailable()) {
				$alert = new AlertBackInStock($this->product);
				$this->services->getManager()->persist($alert);
			}
		}

		// check whether the product is now on sale
		if ($this->existing->getSale() != $this->product->getSale()) {

			$changed = true;
				
			// create a now on sale alert if the product wasn't on sale before
			if (!$this->existing->getSale()) {
				$alert = new AlertNowOnSale($this->product);
				$this->services->getManager()->persist($alert);
			}
		}

		// check whether the price has changed
		if ($this->existing->getNow() != $this->product->getNow() && $this->existing->getSale() == $this->product->getSale()) {
			
			$changed = true;
				
			// create a price change alert
			$alert = new AlertFurtherPriceChange($this->product, $this->existing->getNow());
			$this->services->getManager()->persist($alert);
		}

		// check if we've updated the affiliate link
		if (!$this->existing->getAffiliateUrl() && $this->product->getAffiliateUrl()) {
			$changed = true;
		}

		// check whether the stocked sizes have been updated
		if (is_array($this->existing->getStockedSizes()) && count(array_intersect($this->existing->getStockedSizes(), $this->product->getStockedSizes())) != count($this->existing->getStockedSizes())) {

			$changed = true;

			// check each stocked size to see if it's now back in
			foreach ($this->product->getStockedSizes() as $stockedSize) {
				if ($stockedSize !== null && $this->existing->getStockedSizes() && !in_array($stockedSize, $this->existing->getStockedSizes())) {
					$alert = new AlertSizeInStock($this->product, $stockedSize);
					$this->services->getManager()->persist($alert);
				}			
			}
		}

		// timestamp the update if any changes were made and update the original product
		if ($changed) {
			$this->existing->updateFromClone($this->product);
			$this->existing->setUpdated($this->timestamp);
		}

		return $this->existing;
	}

	/**
	 * Handles any further custom functionality straight after the sale products have been parsed; base defaults to do nothing
	 *
	 * @param  array 	$products 	A collection of Product entities
	 */
	protected function salePostProcessing($products) 
	{
		return;
	}

	/**
	 * Handles any further custom functionality straight after the latest products have been parsed; base defaults to do nothing
	 *
	 * @param  array 	$products 	A collection of Product entities
	 */
	protected function latestPostProcessing($products) 
	{
		return;
	}

	/**
	 * Shops eventually remove products from the site, so use this prototype to check that if you can; defaults to no
	 *
	 * @return boolean  			Whether the product has expired or not
	 */
	protected function getExpired()
	{
		return false;
	}

	/**
	 * See if we can update the affiliate link for the product
	 *
	 * @param  string 	$url 	Optional url override to be converted into an affiliate link
	 * @return boolean 			Whether the affiliate link was successfully updated
	 */
	protected function updateAffiliateLink($url=null)
	{
		// get the shop entity from the instantiated product entity
		if ($this->existing) {
			$shop = $this->existing->getShop();
		} else {
			$shop = $this->product->getShop();
		}	

		if ($shop->getLinkshare()) {
			// if a url isn't passed (ie. for individual updates) we can get it from the parser
			!$url and $url = $this->url;
		
			$token = '2B31muHJqQI';
			$affiliateUrl = 'http://click.linksynergy.com/deeplink?id='.$token.'&mid='.$shop->getAffiliateId().'&murl='.rawurlencode($url);
			
			$this->product->setAffiliateUrl($affiliateUrl);
			return true;
		}
			
		return false;
	}

	/**
	 * Similar to updateAffiliateLink, but always uses parameter values and returns the url rather than updating the product
	 *
	 * @param  Product 	$product 	The product entity we're parsing the link for
	 * @param  string 	$url 		Url to be converted into an affiliate link
	 * @return mixed 				The resulting affiliate link if successful or false if failed
	 */
	protected function parseAffiliateLink($product, $url)
	{
		if (is_object($product) && $product->getShop()->getLinkshare()) {

			$token = '2B31muHJqQI';
			$affiliateUrl = 'http://click.linksynergy.com/deeplink?id='.$token.'&mid='.$product->getShop()->getAffiliateId().'&murl='.rawurlencode($url);

			return $affiliateUrl;
		}

		return false;
	}

	/**
	 * Attempt to get the available and in-stock sizes for the product; defaults to successful
	 *
	 * @return boolean 				Whether parsing the sizes was successful
	 */
	protected function getSizes()
	{
		return true;
	}

	/**
	 * Attempt to get the "style with" related products; defaults to successful
	 *
	 * @return boolean 				Whether parsing the "style with" products was successful
	 */
	protected function getStyleWith()
	{
		return true;
	}

	/**
	 * Custom function to build html markup into the product description; default just wraps it all in a <p> if it hasn't already
	 *
	 * @param  string 		$description 	The raw parsed description text
	 * @return string 						The tidied description as html
	 */
	protected function markupDescription($description)
	{
		// wrap the text in a paragraph if it doesn't already contain markup
		!stristr($description, '<p>') and $description = '<p>'.$description.'</p>';
			
		return $description;
	}

	/**
	 * Calculate the thumbnail image url using the image url; defaults to using the image as the thumbnail
	 *
	 * @param  string 		$image 		The url of the product image
	 * @return string 					The calculated url of the thumbnail
	 */
	public function getThumbnailFromImage($image)
	{
		$thumbnail = $image;

		return $thumbnail;
	}
}