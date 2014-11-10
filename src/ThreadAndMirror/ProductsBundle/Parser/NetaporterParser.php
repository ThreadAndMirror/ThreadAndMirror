<?php

namespace ThreadAndMirror\ProductsBundle\Parser;

use Symfony\Component\DomCrawler\Crawler,
	ThreadAndMirror\ProductsBundle\Parser\BaseParser;

class NetaporterParser extends BaseParser
{
	protected $shop = 2;

	protected $selector = '#product-list .product-image';

	protected $urlSale = 'http://www.net-a-porter.com/gb/en/d/sale/{category}?pn={page}';

	protected $selectorSale = 'ul.products > li';

	protected $categoriesSale = array(
		'Clothing'    => 1, 
		'Shoes'       => 2, 
		'Bags'        => 3, 
		'Accessories' => 4, 
		'Lingerie'    => 1,
	);

	protected $urlLatest = 'http://www.net-a-porter.com/Shop/Whats-New?sortBy=newIn';

	/**
	 * Check the product page exists at all
	 */
	protected function getExpired()
	{
		try 
		{
			$stock = $this->services->getCrawler()->filter('.sold-out-not-coming-back')->text();
			return true;
		}
		catch (\Exception $e) { }
		
		try 
		{
			$stock = $this->services->getCrawler()->filter('.sold-out-message')->text();
			return true;
		}
		catch (\Exception $e) { }

		return false;
	}

	/**
	 * Get the core details of the product, such as the name, url, pid etc. (Whilst also checking if the product page exists)
	 */
	protected function getDetails()
	{
		try
		{
			// strip the pid from the product url
			$pid = explode('product/', $this->url);
			$pid = end($pid);
			$pid = explode('/', $pid);
			$pid = reset($pid);
			$this->product->setPid($pid);
 			
			$this->product->setImage('http://cache.net-a-porter.com/images/products/'.$pid.'/'.$pid.'_in_pp.jpg');
			$this->product->setName(ucwords($this->services->getCrawler()->filter('#product-details h2')->text()).' '.$this->services->getCrawler()->filter('#product-details  h1')->text());			
			$this->product->setUrl($this->url);

			// only update the thumbnail if we don't have one already
			if ($this->product->getThumbnail() == null) {
				$this->product->setThumbnail('http://cache.net-a-porter.com/images/products/'.$pid.'/'.$pid.'_in_pp.jpg');
			}

			// try and build a description for the page (Asos only has lists, so this may need tweaking)
			try
			{	
				$description = $this->services->getCrawler()->filter('.tabBody1 .en-desc')->text();
				$this->product->setDescription($description);
			}
			catch (\Exception $e) 
			{ 
				$this->services->getLogger()->warning('Parsing product description failed for '.$this->url.': '.$e->getMessage());
			}

			// confirm the parsing as successfully
			return true;
		}
		catch (\Exception $e)
		{
			// parsing failed
			$this->services->getLogger()->error('Parsing product details failed for '.$this->url.': '.$e->getMessage());
			return false;
		}
	}

	/**
	 * Get the stock availability of the product
	 */
	protected function getAvailability()
	{
		// net-a-porter now has multiple ways of showing an out of stock item
		try 
		{
			$stock = $this->services->getCrawler()->filter('#button-holder > .button > .message')->text();
			$this->product->setAvailable(false);

			return false;
		}
		catch (\Exception $e) { }

		// if the out of stock message isn't there then it's in stock
		$this->product->setAvailable(true);

		return true;
	}

	/**
	 * Get the sale prices for the product
	 */
	protected function getSalePrices()
	{
		// try parsing the sale prices, otherwise parse as a normal if there are no sale prices nodes
		try 
		{
			// *** need a net-a-porter sale to come before we can find the price
			$was = explode('£', $this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparateProduct_lblProductPreviousPrice')->text());
			$was = str_replace(',', '', end($now));
			$now = explode('£', $this->services->getCrawler()->filter('#ctl00_ContentMainPage_ctlSeparateProduct_lblProductPrice')->text());
			$now = str_replace(',', '', end($was));

			$this->product->setNow($now);
			$this->product->setWas($was);
			$this->product->setSale($this->timestamp);

			return true;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	/**
	 * Get the normal price for the product
	 */
	protected function getPrices()
	{
		try
		{
			$now = str_replace(',', '', $this->services->getCrawler()->filter('.price > span')->text());
			$now = preg_replace('/[^0-9,.]/', '', $now);

			$this->product->setNow($now);
			$this->product->setWas(0);
			$this->product->setSale(null);

			return true;
		}
		catch (\Exception $e) 
		{
			// if we couldn't get a price at all then there's a problem - log it an move on
			$this->services->getLogger()->error('Parsing product prices failed for '.$this->url.': '.$e->getMessage());

			return false;
		}
	}

	/**
	 * Get the sizes of the product
	 */
	protected function getSizes()
	{
		try 
		{
			// work out all available sizes if we don't have them already
			if (!count($this->product->getAvailableSizes())) {

				$available = $this->services->getCrawler()->filter('select#sku option')->each(function ($node, $i) {
    
					// create a new crawler for the size dropdown
					$item = new Crawler($node);
					$size = explode('-', $item->text());
					$size = is_array($size) ? reset($size) : $size;

					return $size;
				});

				// remove the label field
				foreach ($available as $key => $size) {
					if ($size == 'Choose Your Size') {
						unset($available[$key]);
					}
				}

				$this->product->setAvailableSizes($available);
			}
			

			// find only the sizes that are in stock
			$stocked = $this->services->getCrawler()->filter('select#sku option')->each(function ($node, $i) {
    
				// create a new crawler for the size dropdown
				$item = new Crawler($node);
				return $item->text();
			});

			// remove the label field and those out of stock
			foreach ($stocked as $key => $size) {
				if (stristr($size, '- sold out') || $size == 'Choose Your Size') {
					unset($stocked[$key]);
				} else {
					$stocked[$key] = explode('-', $stocked[$key]);
					$stocked[$key] = reset($stocked[$key]);
					$stocked[$key] = is_array($stocked[$key]) ? reset($stocked[$key]) : $stocked[$key];
				}
			}

			$this->product->setStockedSizes($stocked);

			// mark as OOS if there are no sizes left
			if ($this->product->getAvailableSizes() && !$stocked) {
				$this->product->setAvailable(false);
			} else {
				$this->product->setAvailable(true);
			}

			return true;
		}
		catch (\Exception $e)
		{	
			return false;
		}
	}

	/**
	 * Get the style recommendations for the product
	 */
	protected function getStyleWith()
	{
		try 
		{

			$crawler = $this->services->crawlCustomUrl('http://www.net-a-porter.com/api/styling/products/'.$this->existing->getPid().'/1/outfits.json');
			$json = json_decode($crawler->text());
			$styleWith = array();

			// get the pids from the json data
			foreach ($json->outfits[0]->products as $styleWithProduct) {
				$styleWith[] = $styleWithProduct->slotProductId;
			}

			// convert the urls into product
			foreach ($styleWith as $key => $pid) {
				
				// store the pid if it exists, or crawl if it doesn't
				$product = $this->services->getManager()->getRepository('ThreadAndMirrorProductsBundle:Product')->findOneByPid($pid);

				if (!$product) {
					$product = $this->services->getManager()->getRepository('ThreadAndMirrorProductsBundle:Product')->getProductFromUrl('http://www.net-a-porter.com/product/'.$pid);
				}

				if (is_object($product)) {
					$styleWith[$key] = $product->getId();
				} else { 
					unset($styleWith[$key]);
				}
			}

			$this->product->setStyleWith($styleWith);

			return true;
		}
		catch (\Exception $e)
		{	
			return false;
		}
	}

	/**
	 * Parse the amount of pages
	 */
	protected function getPages()
	{
		if ($this->mode == 'latest') {
			return 1;
		} else {
			try
			{
				$pages = $this->services->getCrawler()->filter('div.pager > a');
				$pages = count($pages);
				$this->pages = $this->services->getCrawler()->filter('div.pager > a')->eq($pages-2)->text();
			}
			catch (\InvalidArgumentException $e) 
			{
				// if the page node doesn't exist then there's only one page
				$this->pages = 1;
			}
		}
		
		return $this->pages;
	}

	/**
	 * Get the core details of the product if it's being parsed from a list (eg. sales or latest additions)
	 */
	protected function getDetailsList($product, $subcrawler)
	{
		try
		{
			// stupid netaporter uses different layout
			if ($this->mode == 'latest') {
				$product->setUrl('http://www.net-a-porter.com'.$subcrawler->filter('div.product-image > a')->attr('href'));
				$product->setName(ucwords($subcrawler->filter('a > img')->attr('alt')));
				$product->setThumbnail('http:'.$subcrawler->filter('a > img')->attr('data-src'));
			} else {
				$product->setUrl('http://www.net-a-porter.com'.$subcrawler->filter('div.product-image > a')->attr('href'));
				$product->setThumbnail('http:'.$subcrawler->filter('div.product-image > a > img')->attr('data-src'));
				$name = explode('Was', $subcrawler->filter('div.description')->text());
				$name = preg_replace('/[^A-Za-z0-9]/', ' ', $name[0]);
				$name = preg_replace('/ +/', ' ', $name);
				$product->setName(trim($name));
			}

			$product->setImage($product->getThumbnail());

			// strip the pid from the product url
			$pid = explode('/product/', $subcrawler->filter('div.product-image > a')->attr('href'));
			$pid = explode('/', end($pid));
			$product->setPid(reset($pid));

			// confirm the parsing as successful
			return true;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	/**
	 * Get the sale prices for the product if it's being parsed from a list
	 */
	protected function getSalePricesList($product, $subcrawler)
	{
		// try parsing the sale prices, otherwise parse as a normal if there are no sale prices nodes
		try 
		{
			$price = explode('Now', $subcrawler->filter('span.price')->text());

			$was = explode('£', $price[0]);
			$was = preg_replace('/[^A-Za-z0-9]/', ' ', $was[1]);
			$was = preg_replace('/ +/', '', $was);

			$now = explode('%', $price[1]);
			$now = explode(' ', $now[0]);
			$now = preg_replace('/[^A-Za-z0-9]/', ' ', $now[0]);
			$now = preg_replace('/ +/', '', $now);

			$product->setNow($now);
			$product->setWas($was);
			$product->setSale($this->timestamp);

			return true;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}		

	/**
	 * Get the normal price for the product if it's being parsed from a list
	 */
	protected function getPricesList($product, $subcrawler)
	{
		// annoying net a porter makes it impossible to get price here :/
		$product->setNow(0);

		return true;
	}

	/**
	 * Now we can get those prices
	 */
	protected function latestPostProcessing($products) 
	{
		// get the prices seperately but maintain order to combine the results
		$prices = $this->services->getCrawler()->filter('#product-list .description')->each(function ($node, $i) {

			// create a new crawler for the product html and create a new entity for it
			$description = new Crawler($node);

			$price = explode('£', $description->filter('span.price')->text());
			$price = str_replace(',', '', end($price));

			return $price;
		});

		// combine prices and products
		for ($i=0; $i < count($prices); $i++) { 
			$products[$i]->setNow($prices[$i]);
		}
	}
}