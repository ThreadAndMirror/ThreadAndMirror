<?php

namespace ThreadAndMirror\ProductsBundle\Controller;

// Dependencies
use Symfony\Bundle\FrameworkBundle\Controller\Controller,
	Symfony\Component\HttpFoundation\Response,
	Symfony\Component\DomCrawler\Crawler;

// Entities
use ThreadAndMirror\ProductsBundle\Entity\Product,
	ThreadAndMirror\ProductsBundle\Entity\Shop;
	

// Exceptions
use Doctrine\ORM\NoResultException,
	ThreadAndMirror\ProductsBundle\Exception\ProductAlreadyParsedException;


class DataController extends Controller
{
	/**
	 * 	Cleanup products that are no longer new
	 */
	public function cleanupLatestAdditionsAction()
	{
		// get all new products that were added over a week
		$em = $this->getDoctrine()->getManager();
		$products = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->getExpiredLatestAdditions();

		// set each product as no longer new
		foreach ($products as $product) {
			$now = new DateTime();
			$product->setNew(false);
			$product->setUpdated($now);
			$em->persist($product);
		}

		$em->flush();

		return new Response('Cleanup complete - '.count($products).' products processed.');
	}

	/**
	 * 	Cleanup duplicate products
	 */
	public function cleanupDuplicateProductsAction($limit=30)
	{
		$date = new \DateTime();

		// get the products
		$em = $this->getDoctrine()->getManager();
		$products = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->findBy(array('deleted' => false), array('checked' => 'ASC'), $limit);

		foreach ($products as $product) {

			$duplicates = $em->getRepository('ThreadAndMirrorProductsBundle:Product')->findBy(array('pid' => $product->getPid(), 'shop' => $product->getShop()), array('added' => 'ASC'));
			$count = 0;

			// loop through the duplicates
			foreach ($duplicates as $duplicate) {

				// update the first (ie. oldest added ) duplicate as the original and delete the rest
				if ($count == 0) {
					$duplicate->setChecked($date);
					$em->persist($duplicate);
				} else {
					$em->remove($duplicate);
				}
				$count++;
			}

			if ($count > 1) {
				echo ($count-1).' duplicates removed for ID '.$product->getId().'<br>';
			}
			
		}

		$em->flush();

		return new Response('Cleanup complete - '.count($products).' products processed.');
	}

	public function updateTopshopSales($em, $shop, $existing, $parser)
	{
		$results = 0;

		// parse topshop and convert the html into a crawler object
		$html = $parser->execHtml('www.topshop.com/en/tsuk/category/sale-offers-436/sale-799?pageSize=200');
		$crawler = new Crawler($html);

		// check how many products need parsing to calculate how many pages need crawling
		$total = $crawler->filter('span.product_total')->text();
		$pages = (($total - ($total % 200)) / 200) + 1;

		// iterate through all of the sale product pages on the site
		for ($i=1; $i <= $pages; $i++) { 
			
			// gather the products from the DOM
			$products = $crawler->filter('ul.product')->each(function ($node, $i) {

				// create a new crawler for the product html and create a new entity for it
				$product = new Crawler($node);
				$entity = new Product();

				// catch any items that are showing in the sale list in error by checking for a sale price
				if (count($product->filter('li.now_price'))) {

					// populate the new product entity
					$entity->setPid($product->filter('li.product_image > a')->attr('data-productid'));
					$entity->setName(str_replace('**', '', $product->filter('li.product_description')->text()));
					$entity->setUrl($product->filter('li.product_image > a')->attr('href'));
					$entity->setSale($date);
					$entity->setNew(false);

					// some thumbnails don't have the domain prepended, so strip and re-apply to catch all
					$thumbnail = str_replace('http://media.topshop.com', '', $product->filter('li.product_image > a > img')->attr('src'));
					$entity->setThumbnail('http://media.topshop.com'.$thumbnail);
					$entity->setImage($entity->getThumbnail());

					//strip text from the price strings
					$was = explode('£', $product->filter('li.was_price')->text());
					$now = explode('£', $product->filter('li.now_price')->text());
					$entity->setWas($was[1]);
					$entity->setNow($now[1]);
				}

				return $entity;
			});

			// begin persisting the products until we reach products that we've already covered
			try
			{
				foreach ($products as $product) {
					// check whether the new product is already in the db, if not then add to shop and persist
					if (!in_array($product->getPid(), $existing)) {
						// sometimes the crawler will result in an empty entity, so don't persist
						if ($product->getName()) {
							$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
							$em->persist($product);
							$results++;
						}	
					} else {
						// escape from looping the pages once we find an item that's already added
						throw new ProductAlreadyParsedException();
					}
				}
				$em->flush();
			}
			catch (ProductAlreadyParsedException $e) 
			{
				// flush the final new products before killing the loop
				$em->flush();
				break;
			}

			// get the next page of results if we're not on the last page
			if ($i < $pages) {
				$html = $parser->execHtml('www.topshop.com/en/tsuk/category/sale-offers-436/sale-799?pageSize=200&beginIndex='.(($i*200)+1));
				$crawler = new Crawler($html);			
			} else {
				// escape the loop if we've processed the last page
				break;
			}
		}
		return $results;
	}

	public function updateNetaporterSales($em, $shop, $existing, $parser)
	{
		$results = 0;

		// As net-a-porter splits its sale pages into categories, we need to iterate through each one
		$categories = array('Shoes', 'Clothing', 'Bags', 'Accessories', 'Lingerie');

		foreach ($categories as $category) {
			
			// parse topshop and convert the html into a crawler object
			$html = $parser->execHtml('http://www.net-a-porter.com/gb/en/d/sale/'.$category.'?pn=1');
			$crawler = new Crawler($html);

			// check how many pages need crawling
			$pages = $crawler->filter('div.pagination-links > a')->last()->text();

			// iterate through all of the sale product pages on the site
			for ($i=1; $i <= $pages; $i++) { 
				
				// gather the products from the DOM
				$products = $crawler->filter('ul.products > li')->each(function ($node, $i) {

					// create a new crawler for the product html and create a new entity for it
					$product = new Crawler($node);
					$entity = new Product();

					// populate the new product entity
					$entity->setThumbnail('http:'.$product->filter('div.product-image > a > img')->attr('data-src'));
					$entity->setImage($entity->getThumbnail());
					$entity->setUrl('http://www.net-a-porter.com'.$product->filter('div.product-image > a')->attr('href'));

					// get the product name by combining designer with description
					$name = explode('Was', $product->filter('div.description')->text());
					$name = preg_replace('/[^A-Za-z0-9]/', ' ', $name[0]);
					$name = preg_replace('/ +/', ' ', $name);
					$entity->setName(trim($name));
					
					// strip out the prices in a similar manner
					$price = explode('Now', $product->filter('span.price')->text());

					$was = explode('£', $price[0]);
					$was = preg_replace('/[^A-Za-z0-9]/', ' ', $was[1]);
					$was = preg_replace('/ +/', '', $was);
					$entity->setWas(trim($was));

					$now = explode('%', $price[1]);
					$now = explode(' ', $now[0]);
					$now = preg_replace('/[^A-Za-z0-9]/', ' ', $now[0]);
					$now = preg_replace('/ +/', '', $now);
					$entity->setNow(trim($now));

					// strip the pid from the product url
					$pid = explode('/', $product->filter('div.product-image > a')->attr('href'));
					$entity->setPid(end($pid));

					return $entity;
				});

				// begin persisting the products until we reach products that we've already covered
				try
				{
					foreach ($products as $product) {
						// check whether the new product is already in the db, if not then add to shop and persist
						if (!in_array($product->getPid(), $existing)) {
							// sometimes the crawler will result in an empty entity, so don't persist
							if ($product->getName()) {
								$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
								$em->persist($product);
								$results++;
							}	
						} else {
							// escape from looping the pages once we find an item that's already added
							throw new ProductAlreadyParsedException();
						}
					}
					$em->flush();
				}
				catch (ProductAlreadyParsedException $e) 
				{
					// flush the final new products before killing the loop
					$em->flush();
					break;
				}

				// get the next page of results if we're not on the last page
				if ($i < $pages) {
					$html = $parser->execHtml('http://www.net-a-porter.com/gb/en/d/sale/'.$category.'?pn='.($i+1));
					$crawler = new Crawler($html);			
				} else {
					// escape the loop if we've processed the last page
					break;
				}
			}
		}

		return $results;
	}

	public function updateMatchesSales($em, $shop, $existing, $parser)
	{
		$results = 0;

		// matches sale pages are split into mens and womens
		$categories = array('womens');	

		// crawl through each category
		foreach ($categories as $category) {
			
			// parse the first page and convert the html into a crawler object
			$html = $parser->execHtml('http://www.matchesfashion.com/'.$category.'/sale?pagesize=60');
			$crawler = new Crawler($html);
			
			// check how many pages need crawling 
			$pages = $crawler->filter('div.pager > a');
			$pages = count($pages);
			$pages = $crawler->filter('div.pager > a')->eq($pages-2)->text();

			// iterate through all of the sale product pages on the site
			for ($i=1; $i <= $pages; $i++) {
				
				// gather the products from the DOM
				$products = $crawler->filter('div.products > div.product')->each(function ($node, $i) {

					// create a new crawler for the product html and create a new entity for it
					$product = new Crawler($node);
					$entity = new Product();

					// populate the new product entity
					$entity->setName($product->filter('h4.designer')->text().' '.$product->filter('div.description')->text());
					$entity->setUrl('http://www.matchesfashion.com'.$product->filter('div.product > a')->eq(2)->attr('href'));
					$entity->setThumbnail($product->filter('img.product-image')->attr('src'));
					$entity->setImage($entity->getThumbnail());
					
					// strip the pid from the product url
					$pid = explode('/', $entity->getUrl());
					$entity->setPid(end($pid));

					//strip text from the price strings
					$was = explode('£', $product->filter('div.details > div.price > span.full')->text());
					$was = str_replace(',', '', end($was));
					$entity->setWas($was);

					$now = explode('£', $product->filter('div.details > div.price > span.sale')->text());
					$now = str_replace(',', '', end($now));
					$entity->setNow($now);

					return $entity;
				});

				// begin persisting the products until we reach products that we've already covered
				try
				{
					foreach ($products as $product) {
						// check whether the new product is already in the db, if not then add to shop and persist
						if (!in_array($product->getPid(), $existing)) {
							// sometimes the crawler will result in an empty entity, so don't persist
							if ($product->getName()) {
								$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
								$em->persist($product);
								$results++;
							}	
						} else {
							// escape from looping the pages once we find an item that's already added
							throw new ProductAlreadyParsedException();
						}
					}
					$em->flush();
				}
				catch (ProductAlreadyParsedException $e) 
				{
					// flush the final new products before killing the loop
					$em->flush();
					break;
				}

				// get the next page of results if we're not on the last page
				if ($i < $pages) {
					$html = $parser->execHtml('http://www.matchesfashion.com/'.$category.'/sale?pagesize=60&pagenumber='.($i+1));
					$crawler = new Crawler($html);
				} else {
					// escape the loop if we've processed the last page
					break;
				}
			}
		}
		return $results;
	}

	public function updateAsosSales($em, $shop, $existing, $parser)
	{
		$results = 0;

		// As Asos splits its sale pages into categories, we need to iterate through each one
		$categories = array('11625' => 'Designer');

		foreach ($categories as $cid => $category) {
			
			// parse topshop and convert the html into a crawler object
			$html = $parser->execHtml('http://www.asos.co.uk/Women/Sale/'.$category.'/Cat/pgecategory.aspx?cid='.$cid.'&pge=0&pgesize=204&sort=-1');
			$crawler = new Crawler($html);

			// check how many pages need crawling
			$total = $crawler->filter('span.total-items')->text();
			$pages = (($total - ($total % 204)) / 204) + 1;

			// iterate through all of the sale product pages on the site
			for ($i=1; $i <= $pages; $i++) { 
				
				// gather the products from the DOM
				$products = $crawler->filter('ul#items > li')->each(function ($node, $i) {

					// create a new crawler for the product html and create a new entity for it
					$product = new Crawler($node);
					$entity = new Product();

					// populate the new product entity
					$entity->setThumbnail($product->filter('img.product-image')->attr('src'));
					$entity->setImage($entity->getThumbnail());
					$entity->setName($product->filter('img.product-image')->attr('alt'));
					$entity->setUrl('http://www.asos.com'.$product->filter('a.productImageLink')->attr('href'));
					$entity->setSale($date);
					$entity->setNew(false);

					// try to get the prices, but if there's a node exception then we need to parse designer prices differently
					// try 
					// {
						$was = explode('£', $product->filter('#ctl00_ContentMainPage_ctlSeparateProduct_lblProductPrice')->text());
						$was = str_replace(',', '', end($was));
						$entity->setWas($was);

						$now = explode('£', $product->filter('#ctl00_ContentMainPage_ctlSeparate1_lblProductPreviousPrice')->text());
						$now = str_replace(',', '', end($now));
						$entity->setNow($now);
					// }
					// catch (\InvalidArgumentException $e) 
					// {
					// 	$was = explode('£', $product->filter('div.productprice > span.rrp')->text());
					// 	$was = str_replace(',', '', end($was));
					// 	$entity->setWas($was);

					// 	$now = explode('£', $product->filter('div.productprice > span.price')->text());
					// 	$now = str_replace(',', '', end($now));
					// 	$entity->setNow($now);
					// }

					// strip the pid from the product url
					$pid = explode('iid=', $entity->getUrl());
					$pid = explode('&cid=', end($pid));
					$entity->setPid(reset($pid));

					return $entity;
				});

				// begin persisting the products until we reach products that we've already covered
				try
				{
					foreach ($products as $product) {
						// check whether the new product is already in the db, if not then add to shop and persist
						if (!in_array($product->getPid(), $existing)) {
							// sometimes the crawler will result in an empty entity, so don't persist
							if ($product->getName()) {
								$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
								$em->persist($product);
								$results++;
							}	
						} else {
							// escape from looping the pages once we find an item that's already added
							throw new ProductAlreadyParsedException();
						}
					}
					$em->flush();
				}
				catch (ProductAlreadyParsedException $e) 
				{
					// flush the final new products before killing the loop
					$em->flush();
					break;
				}

				// get the next page of results if we're not on the last page
				if ($i < $pages) {					
					$html = $parser->execHtml('http://www.asos.co.uk/Women/Sale/'.$category.'/Cat/pgecategory.aspx?cid='.$cid.'&pge='.$i.'&pgesize=204&sort=-1');
					$crawler = new Crawler($html);			
				} else {
					// escape the loop if we've processed the last page
					break;
				}
			}
		}
		return $results;
	}

	public function updateBrownsSales($em, $shop, $existing, $parser)
	{
		$results = 0;

		// crawl through the indivudal categories until I sort the ajax pagination it has
		$categories = array(
			'clothing/dresses' => 1, 
			'clothing/tops' => 1, 
			'clothing/shorts' => 1, 
			'clothing/coats' => 1, 
			'clothing/skirts' => 1, 
			'clothing/jackets' => 1, 
			'clothing/jeans' => 1, 
			'clothing/knitwear' => 1,
			'clothing/swimwear' => 1,
			'clothing/hosiery_and_leggings' => 1,
			'clothing/t-shirts' => 1,
			'clothing/jumpsuits' => 1,
			'clothing/trousers' => 1,
			'accessories/bags' => 3,
			'accessories/shoes_and_boots' => 2,
			'accessories/jewellery' => 4,
			'accessories/other_accessories' => 4,
		);	

		// crawl through each category
		foreach ($categories as $categoryUrl => $categoryInternal) {

			// parse the first page and convert the html into a crawler object
			$html = $parser->execHtml('http://www.brownsfashion.com/products/sale-women/'.$categoryUrl);
			$crawler = new Crawler($html);

			// check how many pages need crawling
			$pages = 1;

			// iterate through all of the sale product pages on the site
			for ($i=1; $i <= $pages; $i++) {
				
				// gather the products from the DOM
				$products = $crawler->filter('a.itm')->each(function ($node, $i) {

					// create a new crawler for the product html and create a new entity for it
					$product = new Crawler($node);
					$entity = new Product();

					// populate the new product entity
					$entity->setName(ucwords(strtolower(htmlspecialchars_decode($product->filter('h5')->text().' '.$product->filter('h6')->text(), ENT_QUOTES))));
					$entity->setUrl('http://www.brownsfashion.com'.$product->attr('href'));
					$entity->setThumbnail($product->filter('img')->attr('src'));
					$entity->setImage($entity->getThumbnail());
					$entity->setNew(false);

					// strip the pid from the product url
					$pid = explode('/product/', $entity->getUrl());
					$entity->setPid(end($pid));

					// catch any funny designer items that are caught by the sale parser
					try
					{
						// parse the prices
						$was = str_replace(',', '', $product->filter('div.i_p > span.p_pr > span.currency-value')->text());
						$was = preg_replace('/[^0-9,.]/', '', $was);
						$entity->setWas($was);
						$entity->setSale($date);
						$now = str_replace(',', '', $product->filter('div.i_p > span.p_sale > span.currency-value')->text());
						$now = preg_replace('/[^0-9,.]/', '', $now);
						$entity->setNow($now);
					}
					catch (\InvalidArgumentException $e) 
					{
						$now = str_replace(',', '', $product->filter('.currUpdate > span.currency-value')->text());
						$now = preg_replace('/[^0-9,.]/', '', $now);
						$entity->setNow($now);
						$entity->setWas(0);
						$entity->setSale(null);
					}

					return $entity;
				});
			}

			// begin persisting the products until we reach products that we've already covered
			try
			{
				foreach ($products as $product) {
					// check whether the new product is already in the db, if not then add to shop and persist
					if (!in_array($product->getPid(), $existing)) {
						// sometimes the crawler will result in an empty entity, so don't persist
						if ($product->getName()) {
							$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
							$product->setCategory($categoryInternal);
							$em->persist($product);
							$results++;
						}	
					} else {
						// escape from looping the pages once we find an item that's already added
						throw new ProductAlreadyParsedException();
					}
				}
				$em->flush();
			}
			catch (ProductAlreadyParsedException $e) 
			{
				// flush the final new products before killing the loop
				$em->flush();
				break;
			}

			// get the next page of results if we're not on the last page
			// if ($i < $pages) {
			// 	$html = $parser->execHtml('http://www.brownsfashion.com/products/whats_new/whats_new/clothing/whats_new_for_women'.($i+1));
			// 	$crawler = new Crawler($html);	
			// } else {
			// 	// escape the loop if we've processed the last page
			// 	break;
			// }
		}
		return $results;
	}

	public function updateFarfetchSales($em, $shop, $existing, $parser)
	{
		$results = 0;

		// As farfetch splits its sale pages into categories, we need to iterate through each one
		$categories = array(
			'bags-purses-1' => 3, 
			'shoes-1' => 2, 
			'jewellery-1' => 4, 
			'accessories-1' => 5, 
			'clothing-1' => 1,
		);

		foreach ($categories as $categoryUrl => $categoryInternal) {
			
			// parse the first page and convert the html into a crawler object
			$html = $parser->execHtml('http://www.farfetch.com/shopping/sale/women/'.$categoryUrl.'/pv-180/ps-1/items.aspx');
			$crawler = new Crawler($html);

			// check how many pages need crawling
			$pages = $crawler->filter('#listingPaging > ul > li'); 
			$pages = count($pages);

			// iterate through all of the sale product pages on the site
			for ($i=1; $i <= $pages; $i++) {
				
				// gather the products from the DOM
				$products = $crawler->filter('.listingItemWrap')->each(function ($node, $i) {

					// create a new crawler for the product html and create a new entity for it
					$product = new Crawler($node);
					$entity = new Product();
					$entity->setNew(false);
					$entity->setSale($date);

					// populate the new product entity
					$entity->setName(ucwords(str_replace('-', '', $product->filter('.ProductImage')->attr('title'))));
					$entity->setUrl('http://www.farfetch.com'.$product->filter('a')->attr('href'));
					$entity->setThumbnail($product->filter('.ProductImage')->attr('src'));
					$entity->setImage($entity->getThumbnail());
					
					// strip the pid from the product url
					$entity->setPid($product->attr('data-item-id'));

					// parse the prices
					$was = str_replace('£', '', $product->filter('.listingPrice > strike')->text());
					$was = str_replace(',', '', $was); 
					$was = preg_replace('/[^0-9,.]/', '', $was);

					$now = str_replace('£', '', $product->filter('.listingPrice > .saleprice')->text());
					$now = str_replace(',', '', $now); 
					$now = preg_replace('/[^0-9,.]/', '', $now);

					$entity->setNow($now);
					$entity->setWas($was);	

					return $entity;
				});
				

				// begin persisting the products until we reach products that we've already covered
				try
				{
					foreach ($products as $product) {
						// check whether the new product is already in the db, if not then add to shop and persist
						if (!in_array($product->getPid(), $existing)) {
							// sometimes the crawler will result in an empty entity, so don't persist
							if ($product->getName()) {
								$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
								$product->setCategory($categoryInternal);
								$em->persist($product);
								$results++;
							}	
						} else {
							// escape from looping the pages once we find an item that's already added
							throw new ProductAlreadyParsedException();
						}
					}
					$em->flush();
				}
				catch (ProductAlreadyParsedException $e) 
				{
					// flush the final new products before killing the loop
					$em->flush();
					break;
				}

				// get the next page of results if we're not on the last page
				if ($i < $pages) {
					$html = $parser->execHtml('http://www.farfetch.com/shopping/sale/women/'.$categoryUrl.'/pv-180/ps-'.($i+1).'/items.aspx');
					$crawler = new Crawler($html);	
				} else {
					// escape the loop if we've processed the last page
					break;
				}
			}
		}
		return $results;
	}

	public function updateThecornerSales($em, $shop, $existing, $parser)
	{
		$results = 0;

		// parse the first page and convert the html into a crawler object
		$html = $parser->execHtml('http://www.thecorner.com/gb/women/sale?page=1');
		$crawler = new Crawler($html);

		// check how many pages need crawling
		$total = $crawler->filter('.nbHowMany > b')->text();
		$pages = (($total - ($total % 30)) / 30) + 1;

		// iterate through all of the sale product pages on the site
		for ($i=1; $i <= $pages; $i++) {
			
			// gather the products from the DOM
			$products = $crawler->filter('.itemThumb')->each(function ($node, $i) {

				// create a new crawler for the product html and create a new entity for it
				$product = new Crawler($node);
				$entity = new Product();

				// populate the new product entity
				$entity->setName(ucwords(strtolower($product->filter('.itemBrandAndCat > .brand')->text().' '.$product->filter('.itemBrandAndCat > .category')->text())));
				$entity->setUrl('http://www.thecorner.com'.$product->filter('a.itemContainer')->attr('href'));
				$entity->setThumbnail($product->filter('a.itemContainer > img')->attr('src'));
				$entity->setImage($entity->getThumbnail());
				$entity->setNew(false);
				$entity->setSale($date);
				
				// strip the pid from the product url
				$pid = explode('_cod', $entity->getUrl());
				$pid = str_replace('.html', '', end($pid));
				$entity->setPid($pid);

				// parse the price
				$now = preg_replace('/[^0-9,.]/', '', $product->filter('.newprice')->text());
				$now = str_replace(',', '', $now); 
				$entity->setNow($now);
				$was = preg_replace('/[^0-9,.]/', '', $product->filter('.oldprice')->text());
				$was = str_replace(',', '', $was); 
				$entity->setWas($was);

				return $entity;
			});

			// begin persisting the products until we reach products that we've already covered
			try
			{
				foreach ($products as $product) {
					// check whether the new product is already in the db, if not then add to shop and persist
					if (!in_array($product->getPid(), $existing)) {
						// sometimes the crawler will result in an empty entity, so don't persist
						if ($product->getName()) {
							$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
							$em->persist($product);
							$results++;
						}	
					} else {
						// escape from looping the pages once we find an item that's already added
						throw new ProductAlreadyParsedException();
					}
				}
				$em->flush();
			}
			catch (ProductAlreadyParsedException $e) 
			{
				// flush the final new products before killing the loop
				$em->flush();
				break;
			}

			// get the next page of results if we're not on the last page
			if ($i < $pages) {
				$html = $parser->execHtml('http://www.thecorner.com/gb/women/sale?page='.($i+1));
				$crawler = new Crawler($html);			
			} else {
				// escape the loop if we've processed the last page
				break;
			}
		}
		return $results;
	}
	
	public function updateLondonboutiquesSales($em, $shop, $existing, $parser)
	{
		$results = 0;

		// parse the first page and convert the html into a crawler object
		$html = $parser->execHtml('http://www.london-boutiques.com/sale?limit=120&p=1');
		$crawler = new Crawler($html);

		// check how many pages need crawling
		$pages = (count($crawler->filter('.pages > ol > li')) / 2) - 1;

		// iterate through all of the sale product pages on the site
		for ($i=1; $i <= $pages; $i++) {
			
			// gather the products from the DOM
			$products = $crawler->filter('ul.products-grid > li.item')->each(function ($node, $i) {

				// create a new crawler for the product html and create a new entity for it
				$product = new Crawler($node);
				$entity = new Product();

				// populate the new product entity
				$entity->setName(ucwords(htmlspecialchars_decode(utf8_encode($product->filter('h2.product-name')->text().' '.$product->filter('.shortdesc')->text()), ENT_QUOTES)));
				$entity->setUrl($product->filter('a')->attr('href'));
				$entity->setThumbnail($product->filter('img.front2')->attr('src'));
				$entity->setImage($entity->getThumbnail());
				$entity->setNew(false);
				$entity->setSale($date);

				// strip the pid from the product price field id
				$pid = explode('-', $product->filter('.price-box > .old-price > .price')->attr('id'));
				$entity->setPid(end($pid));

				// parse the price
				$was = str_replace(',', '', $product->filter('.price-box > .old-price > .price')->text());
				$was = preg_replace('/[^0-9,.]/', '', $was);
				$entity->setWas($was);
				$now = str_replace(',', '', $product->filter('.price-box > .special-price > .price')->text());
				$now = preg_replace('/[^0-9,.]/', '', $now);
				$entity->setNow($now);

				return $entity;
			});

			// begin persisting the products until we reach products that we've already covered
			try
			{
				foreach ($products as $product) {
					// check whether the new product is already in the db, if not then add to shop and persist
					if (!in_array($product->getPid(), $existing)) {
						// sometimes the crawler will result in an empty entity, so don't persist
						if ($product->getName()) {
							$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
							$em->persist($product);
							$results++;
						}	
					} else {
						// escape from looping the pages once we find an item that's already added
						throw new ProductAlreadyParsedException();
					}
				}
				$em->flush();
			}
			catch (ProductAlreadyParsedException $e) 
			{
				// flush the final new products before killing the loop
				$em->flush();
				break;
			}

			// get the next page of results if we're not on the last page
			if ($i < $pages) {
				$html = $parser->execHtml('http://www.london-boutiques.com/sale?limit=120&p='.($i+1));
				$crawler = new Crawler($html);	
			} else {
				// escape the loop if we've processed the last page
				break;
			}
		}
		return $results;
	}

	public function updateMytheresaSales($em, $shop, $existing, $parser)
	{
		$results = 0;

		// crawl through the indivudal categories
		$categories = array(
			'clothing' => 1, 
			'shoes' => 2,
			'bags' => 3,
			'accessories' => 4,
		);	

		// crawl through each category
		foreach ($categories as $categoryUrl => $categoryInternal) {

			// parse the first page and convert the html into a crawler object
			$html = $parser->execHtml('http://www.mytheresa.com/en-gb/sale/'.$categoryUrl.'.html');
			$crawler = new Crawler($html);

			// count how many pages need crawling
			$pages = 1;

			// iterate continually until we break the loop
			while (true) {
				
				// gather the products from the DOM
				$products = $crawler->filter('ul.products-grid > li.item')->each(function ($node, $i) {

					// create a new crawler for the product html and create a new entity for it
					$product = new Crawler($node);
					$entity = new Product();

					// populate the new product entity
					$entity->setName(ucwords(htmlspecialchars_decode($product->filter('h2.designer-name')->text().' '.strtolower($product->filter('h3.product-name')->text()), ENT_QUOTES)));
					$entity->setUrl($product->filter('a')->attr('href'));
					$entity->setThumbnail($product->filter('img.image1st')->attr('src'));
					$entity->setImage($entity->getThumbnail());
					$entity->setNew(false);
					$entity->setSale($date);

					// parse the price, but catch any exceptions as some idiots put non-sale items in the sale section
					try
					{
						// strip the pid from the product price field id
						$pid = explode('-', $product->filter('.price-box > .old-price > .price')->attr('id'));
						$entity->setPid(end($pid));
						
						$was = str_replace(',', '', $product->filter('.price-box > .old-price > .price')->text());
						$was = preg_replace('/[^0-9,.]/', '', $was);
						$entity->setWas($was);
						$now = str_replace(',', '', $product->filter('.price-box > .special-price > .price')->text());
						$now = preg_replace('/[^0-9,.]/', '', $now);
						$entity->setNow($now);

						return $entity;
					}
					catch (\InvalidArgumentException $e) 
					{
						unset($entity);
						return new Product();
					}
				});

				// begin persisting the products until we reach products that we've already covered
				try
				{
					foreach ($products as $product) {
						// check whether the new product is already in the db, if not then add to shop and persist
						if (!in_array($product->getPid(), $existing)) {
							// sometimes the crawler will result in an empty entity, so don't persist
							if ($product->getName()) {
								$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
								$product->setCategory($categoryInternal);
								$em->persist($product);
								$results++;

								// output a log entry
								echo 'Added '.$product->getPid().' ('.$product->getUrl().')<br>';
							}	
						} else {
							// escape from looping the pages once we find an item that's already added
							throw new ProductAlreadyParsedException();

						}
					}
					$em->flush();
					$em->clear();
					unset($products);
					echo 'EM flushed, current memory: ('.memory_get_usage().')<br>';
				}
				catch (ProductAlreadyParsedException $e) 
				{
					// flush the final new products before killing the loop
					$em->flush();
					$em->clear();
					unset($products);
					echo 'EM flushed, current memory: ('.memory_get_usage().')<br>';
					break;
				}

				// get the next page of results if we're not on the last page (ie. if the next button doesn't exist)
				try 
				{
					$next = $crawler->filter('.i-next')->text();
					$pages++;
					$parser->resetHandle();
					$html = $parser->execHtml('http://www.mytheresa.com/en-gb/sale/'.$categoryUrl.'.html?p='.$pages);
					$crawler = new Crawler($html);	
				} 
				catch (\InvalidArgumentException $e)
				{
					// escape the loop if we've processed the last page
					break;
				}
			}
		}
		return $results;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////// LATEST ADDITION PARSERS ///////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////

	public function updateTopshopLatest($em, $shop, $existing, $parser)
	{
		$results = 0;

		// parse topshop and convert the html into a crawler object
		$html = $parser->execHtml('http://www.topshop.com/en/tsuk/category/new-in-this-week-2169932/new-in-this-week-493?pageSize=200&beginIndex=1');
		$crawler = new Crawler($html);

		// check how many products need parsing to calculate how many pages need crawling
		$total = $crawler->filter('span.product_total')->text();
		$pages = (($total - ($total % 200)) / 200) + 1;

		// iterate through all of the sale product pages on the site
		for ($i=1; $i <= $pages; $i++) { 
			
			// gather the products from the DOM
			$products = $crawler->filter('ul.product')->each(function ($node, $i) {

				// create a new crawler for the product html and create a new entity for it
				$product = new Crawler($node);
				$entity = new Product();

				// populate the new product entity
				$entity->setPid($product->filter('li.product_image > a')->attr('data-productid'));
				$entity->setName(str_replace('**', '', $product->filter('li.product_description')->text()));
				$entity->setUrl($product->filter('li.product_image > a')->attr('href'));
				$entity->setNew(true);
				$entity->setSale(null);

				// some thumbnails don't have the domain prepended, so strip and re-apply to catch all
				$thumbnail = str_replace('http://media.topshop.com', '', $product->filter('li.product_image > a > img')->attr('src'));
				$entity->setThumbnail('http://media.topshop.com'.$thumbnail);
				$entity->setImage($entity->getThumbnail());

				//strip text from the price strings
				$now = $product->filter('li.product_price')->text();
				$now = preg_replace('/[^0-9,.]/', '', $now);
				$entity->setWas(null);
				$entity->setNow($now);

				return $entity;
			});

			// begin persisting the products until we reach products that we've already covered
			try
			{
				foreach ($products as $product) {
					// check whether the new product is already in the db, if not then add to shop and persist
					if (!in_array($product->getPid(), $existing)) {
						// sometimes the crawler will result in an empty entity, so don't persist
						if ($product->getName()) {
							$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
							$em->persist($product);
							$results++;
						}	
					} else {
						// escape from looping the pages once we find an item that's already added
						throw new ProductAlreadyParsedException();
					}
				}
				$em->flush();
			}
			catch (ProductAlreadyParsedException $e) 
			{
				// flush the final new products before killing the loop
				$em->flush();
				break;
			}

			// get the next page of results if we're not on the last page
			if ($i < $pages) {
				$html = $parser->execHtml('http://www.topshop.com/en/tsuk/category/new-in-this-week-2169932/new-in-this-week-493?pageSize=200&beginIndex='.(($i*200)+1));
				$crawler = new Crawler($html);			
			} else {
				// escape the loop if we've processed the last page
				break;
			}
		}
		return $results;
	}

	public function updateNetaporterLatest($em, $shop, $existing, $parser)
	{
		$results = 0;

		// parse net a porter and convert the html into a crawler object
		$html = $parser->execHtml('http://www.net-a-porter.com/Shop/Whats-New');
		$crawler = new Crawler($html);
			
		// gather the products from the DOM
		$products = $crawler->filter('#product-list .product-image')->each(function ($node, $i) {

			// create a new crawler for the product html and create a new entity for it
			$product = new Crawler($node);
			$entity = new Product();

			// populate the new product entity
			$entity->setThumbnail('http:'.$product->filter('a > img')->attr('data-src'));
			$entity->setImage($entity->getThumbnail());
			$entity->setUrl('http://www.net-a-porter.com'.$product->filter('a')->attr('href'));
			$entity->setName(ucwords($product->filter('a > img')->attr('alt')));
			$entity->setWas(null);

			// annoying net a porter makes it impossible to get price here :/
			$entity->setNow(0);

			// strip the pid from the product url
			$pid = explode('/', $product->filter('div.product-image > a')->attr('href'));
			$entity->setPid(end($pid));
			$entity->setSale(null);
			$entity->setNew(true);

			return $entity;
		});

		// get the prices seperately but maintain order to combine the results
		$prices = $crawler->filter('#product-list .description')->each(function ($node, $i) {

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

		// begin persisting the products until we reach products that we've already covered
		try
		{
			foreach ($products as $product) {
				// check whether the new product is already in the db, if not then add to shop and persist
				if (!in_array($product->getPid(), $existing)) {
					// sometimes the crawler will result in an empty entity, so don't persist
					if ($product->getName()) {
						$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
						$em->persist($product);
						$results++;
					}	
				} else {
					// escape from looping the pages once we find an item that's already added
					throw new ProductAlreadyParsedException();
				}
			}
			$em->flush();
		}
		catch (ProductAlreadyParsedException $e) 
		{
			// flush the final new products
			$em->flush();
		}

		return $results;
	}

	public function updateMatchesLatest($em, $shop, $existing, $parser)
	{
		$results = 0;

		// parse the first page and convert the html into a crawler object
		$html = $parser->execHtml('http://www.matchesfashion.com/womens/justin/last-7-days?pagesize=240');
		$crawler = new Crawler($html);

		// check how many pages need crawling...
		try
		{
			$pages = $crawler->filter('div.pager > a');
			$pages = count($pages);
			$pages = $crawler->filter('div.pager > a')->eq($pages-2)->text();
		}
		catch (\InvalidArgumentException $e) 
		{
			// ...but if the page node doesn't exist then there's only one page
			$pages = 1;
		}

		// iterate through all of the sale product pages on the site
		for ($i=1; $i <= $pages; $i++) {
			
			// gather the products from the DOM
			$products = $crawler->filter('div.products > div.product')->each(function ($node, $i) {

				// create a new crawler for the product html and create a new entity for it
				$product = new Crawler($node);
				$entity = new Product();

				// populate the new product entity
				$entity->setName(ucwords($product->filter('h4.designer')->text().' '.$product->filter('div.description')->text()));
				$entity->setUrl('http://www.matchesfashion.com'.$product->filter('div.product > a')->eq(2)->attr('href'));
				$entity->setThumbnail($product->filter('img.product-image')->attr('src'));
				$entity->setImage($entity->getThumbnail());
				$entity->setNew(true);
				
				// strip the pid from the product url
				$pid = explode('/', $entity->getUrl());
				$entity->setPid(end($pid));

				// parse the price
				$now = str_replace(',', '', $product->filter('div.details > div.price')->text());
				$now = preg_replace('/[^0-9,.]/', '', $now);
				$entity->setNow($now);
				$entity->setWas(0);	
				$entity->setSale(0);

				return $entity;
			});
			
			// begin persisting the products until we reach products that we've already covered
			try
			{
				foreach ($products as $product) {
					// check whether the new product is already in the db, if not then add to shop and persist
					if (!in_array($product->getPid(), $existing)) {
						// sometimes the crawler will result in an empty entity, so don't persist
						if ($product->getName()) {
							$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
							$em->persist($product);
							$results++;
						}	
					} else {
						// escape from looping the pages once we find an item that's already added
						throw new ProductAlreadyParsedException();
					}
				}
				$em->flush();
			}
			catch (ProductAlreadyParsedException $e) 
			{
				// flush the final new products before killing the loop
				$em->flush();
				break;
			}
			
			// get the next page of results if we're not on the last page
			if ($i < $pages) {
				$html = $parser->execHtml('http://www.matchesfashion.com/womens/justin/last-7-days?pagesize=240&pagenumber='.($i+1));
				$crawler = new Crawler($html);			
			} else {
				// escape the loop if we've processed the last page
				break;
			}
		}
		return $results;
	}

	public function updateAsosLatest($em, $shop, $existing, $parser)
	{
		$results = 0;

		// As Asos splits its sale pages into categories, we need to iterate through each one
		$categories = array('2623' => 'New-In-Clothing', '6992' => 'New-In-Shoes-Accs', '6930' => 'new-in-designer');

		foreach ($categories as $cid => $category) {
			
			// parse topshop and convert the html into a crawler object
			$html = $parser->execHtml('http://www.asos.co.uk/Women/'.$category.'/Cat/pgecategory.aspx?cid='.$cid.'&pge=0&pgesize=204&sort=-1');
			$crawler = new Crawler($html);

			// check how many pages need crawling
			$total = $crawler->filter('span.total-items')->text();
			$pages = (($total - ($total % 204)) / 204) + 1;

			// iterate through all of the sale product pages on the site
			for ($i=1; $i <= $pages; $i++) { 
				
				// gather the products from the DOM
				$products = $crawler->filter('ul#items > li')->each(function ($node, $i) {

					// create a new crawler for the product html and create a new entity for it
					$product = new Crawler($node);
					$entity = new Product();

					// populate the new product entity
					$entity->setThumbnail($product->filter('img.product-image')->attr('src'));
					$entity->setImage($entity->getThumbnail());
					$entity->setName($product->filter('img.product-image')->attr('alt'));
					$entity->setUrl('http://www.asos.com'.$product->filter('a.productImageLink')->attr('href'));
					$entity->setWas(0);

					$now = explode('£', $product->filter('div.productprice > span.price')->text());
					$now = str_replace(',', '', end($now));
					$entity->setNow($now);

					// designer items show an RRP price instead, so get that
					if ($entity->getNow() == 0) {
						$now = explode('£', $product->filter('.product_rrp > span')->text());
						$now = str_replace(',', '', end($now));
						$entity->setNow($now);
					}

					// strip the pid from the product url
					$pid = explode('iid=', $entity->getUrl());
					$pid = explode('&cid=', end($pid));
					$entity->setPid(reset($pid));

					$entity->setSale(null);
					$entity->setNew(true);

					return $entity;
				});

				// begin persisting the products until we reach products that we've already covered
				try
				{
					foreach ($products as $product) {
						// check whether the new product is already in the db, if not then add to shop and persist
						if (!in_array($product->getPid(), $existing)) {
							// sometimes the crawler will result in an empty entity, so don't persist
							if ($product->getName()) {
								$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
								$em->persist($product);
								$results++;
							}	
						} else {
							// escape from looping the pages once we find an item that's already added
							throw new ProductAlreadyParsedException();
						}
					}
					$em->flush();
				}
				catch (ProductAlreadyParsedException $e) 
				{
					// flush the final new products before killing the loop
					$em->flush();
					break;
				}

				// get the next page of results if we're not on the last page
				if ($i < $pages) {					
					$html = $parser->execHtml('http://www.asos.co.uk/Women/'.$category.'/Cat/pgecategory.aspx?cid='.$cid.'&pge='.$i.'&pgesize=204&sort=-1');
					$crawler = new Crawler($html);			
				} else {
					// escape the loop if we've processed the last page
					break;
				}
			}
		}
		return $results;
	}

	public function updateBrownsLatest($em, $shop, $existing, $parser)
	{
		$results = 0;

		// parse the first page and convert the html into a crawler object
		$html = $parser->execHtml('http://www.brownsfashion.com/products/whats_new/whats_new/clothing/whats_new_for_women');
		$crawler = new Crawler($html);

		// check how many pages need crawling
		$pages = 1;

		// iterate through all of the sale product pages on the site
		for ($i=1; $i <= $pages; $i++) {
			
			// gather the products from the DOM
			$products = $crawler->filter('a.itm')->each(function ($node, $i) {

				// create a new crawler for the product html and create a new entity for it
				$product = new Crawler($node);
				$entity = new Product();

				// populate the new product entity
				$entity->setName(ucwords(strtolower(htmlspecialchars_decode($product->filter('h5')->text().' '.$product->filter('h6')->text(), ENT_QUOTES))));
				$entity->setUrl('http://www.brownsfashion.com'.$product->attr('href'));
				$entity->setThumbnail($product->filter('img')->attr('src'));
				$entity->setImage($entity->getThumbnail());
				$entity->setNew(true);
				
				// strip the pid from the product url
				$pid = explode('/product/', $entity->getUrl());
				$entity->setPid(end($pid));

				// parse the price
				$now = str_replace(',', '', $product->filter('div.i_p > span.currency-value')->text());
				$now = preg_replace('/[^0-9,.]/', '', $now);
				$entity->setNow($now);
				$entity->setWas(0);	
				$entity->setSale(null);

				return $entity;
			});
			

			// begin persisting the products until we reach products that we've already covered
			try
			{
				foreach ($products as $product) {
					// check whether the new product is already in the db, if not then add to shop and persist
					if (!in_array($product->getPid(), $existing)) {
						// sometimes the crawler will result in an empty entity, so don't persist
						if ($product->getName()) {
							$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
							$em->persist($product);
							$results++;
						}	
					} else {
						// escape from looping the pages once we find an item that's already added
						throw new ProductAlreadyParsedException();
					}
				}
				$em->flush();
			}
			catch (ProductAlreadyParsedException $e) 
			{
				// flush the final new products before killing the loop
				$em->flush();
				break;
			}

			// get the next page of results if we're not on the last page
			if ($i < $pages) {
				$html = $parser->execHtml('http://www.brownsfashion.com/products/whats_new/whats_new/clothing/whats_new_for_women'.($i+1));
				$crawler = new Crawler($html);	
			} else {
				// escape the loop if we've processed the last page
				break;
			}
		}
		return $results;
	}

	public function updateFarfetchLatest($em, $shop, $existing, $parser)
	{
		$results = 0;

		// parse the first page and convert the html into a crawler object
		$html = $parser->execHtml('http://www.farfetch.com/shopping/newin/women/pv-60/ps-1/items.aspx');
		$crawler = new Crawler($html);

		// check how many pages need crawling
		$pages = $crawler->filter('#listingPaging > ul > li');
		$pages = count($pages);

		// iterate through all of the sale product pages on the site
		for ($i=1; $i <= $pages; $i++) {
			
			// gather the products from the DOM
			$products = $crawler->filter('.listingItemWrap')->each(function ($node, $i) {

				// create a new crawler for the product html and create a new entity for it
				$product = new Crawler($node);
				$entity = new Product();

				// populate the new product entity
				$entity->setName(ucwords(str_replace('-', '', $product->filter('.ProductImage')->attr('title'))));
				$entity->setUrl('http://www.farfetch.com'.$product->filter('a')->attr('href'));
				$entity->setThumbnail($product->filter('.ProductImage')->attr('src'));
				$entity->setImage($entity->getThumbnail());
				$entity->setNew(true);
				
				// strip the pid from the product url
				$entity->setPid($product->attr('data-item-id'));

				// parse the price
				$now = str_replace('£', '', $product->filter('.listingPrice')->text());
				$now = str_replace(',', '', $now); 
				$now = preg_replace('/[^0-9,.]/', '', $now);
				$entity->setNow($now);
				$entity->setWas(0);	
				$entity->setSale(0);

				return $entity;
			});
			

			// begin persisting the products until we reach products that we've already covered
			try
			{
				foreach ($products as $product) {
					// check whether the new product is already in the db, if not then add to shop and persist
					if (!in_array($product->getPid(), $existing)) {
						// sometimes the crawler will result in an empty entity, so don't persist
						if ($product->getName()) {
							$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
							$em->persist($product);
							$results++;
						}	
					} else {
						// escape from looping the pages once we find an item that's already added
						throw new ProductAlreadyParsedException();
					}
				}
				$em->flush();
			}
			catch (ProductAlreadyParsedException $e) 
			{
				// flush the final new products before killing the loop
				$em->flush();
				break;
			}

			// get the next page of results if we're not on the last page
			if ($i < $pages) {
				// reset the curl handle after each page, as this site seems to chew through memory
				$html = $parser->execHtml('http://www.farfetch.com/shopping/newin/women/pv-60/ps-'.($i+1).'/items.aspx');
				$crawler = new Crawler($html);			
			} else {
				// escape the loop if we've processed the last page
				break;
			}
		}
		return $results;
	}

	public function updateThecornerLatest($em, $shop, $existing, $parser)
	{
		$results = 0;

		// parse the first page and convert the html into a crawler object
		$html = $parser->execHtml('http://www.thecorner.com/gb/women/new-arrivals?page=1#ipp=30');
		$crawler = new Crawler($html);

		// check how many pages need crawling
		$total = $crawler->filter('.nbHowMany > b')->text();
		$pages = (($total - ($total % 30)) / 30) + 1;

		// iterate through all of the sale product pages on the site
		for ($i=1; $i <= $pages; $i++) {
			
			// gather the products from the DOM
			$products = $crawler->filter('.itemThumb')->each(function ($node, $i) {

				// create a new crawler for the product html and create a new entity for it
				$product = new Crawler($node);
				$entity = new Product();

				// populate the new product entity
				$entity->setName(ucwords(strtolower($product->filter('.itemBrandAndCat > .brand')->text().' '.$product->filter('.itemBrandAndCat > .category')->text())));
				$entity->setUrl('http://www.thecorner.com'.$product->filter('a.itemContainer')->attr('href'));
				$entity->setThumbnail($product->filter('a.itemContainer > img')->attr('src'));
				$entity->setImage($entity->getThumbnail());
				$entity->setNew(true);
				$entity->setSale(null);
				
				// strip the pid from the product url
				$pid = explode('_cod', $entity->getUrl());
				$pid = str_replace('.html', '', end($pid));
				$entity->setPid($pid);

				// parse the price
				$now = preg_replace('/[^0-9,.]/', '', $product->filter('.newprice')->text());
				$now = str_replace(',', '', $now); 
				$entity->setNow($now);
				$entity->setWas(0);	

				return $entity;
			});

			// begin persisting the products until we reach products that we've already covered
			try
			{
				foreach ($products as $product) {
					// check whether the new product is already in the db, if not then add to shop and persist
					if (!in_array($product->getPid(), $existing)) {
						// sometimes the crawler will result in an empty entity, so don't persist
						if ($product->getName()) {
							$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
							$em->persist($product);
							$results++;
						}	
					} else {
						// escape from looping the pages once we find an item that's already added
						throw new ProductAlreadyParsedException();
					}
				}
				$em->flush();
			}
			catch (ProductAlreadyParsedException $e) 
			{
				// flush the final new products before killing the loop
				$em->flush();
				break;
			}

			// get the next page of results if we're not on the last page
			if ($i < $pages) {
				$html = $parser->execHtml('http://www.thecorner.com/gb/women/new-arrivals?page='.($i+1).'#ipp=30');
				$crawler = new Crawler($html);			
			} else {
				// escape the loop if we've processed the last page
				break;
			}
		}
		return $results;
	}

	public function updateLondonboutiquesLatest($em, $shop, $existing, $parser)
	{
		$results = 0;

		// parse the first page and convert the html into a crawler object
		$html = $parser->execHtml('http://www.london-boutiques.com/just-in?limit=120&p=1');
		$crawler = new Crawler($html);

		// check how many pages need crawling
		$pages = 5;

		// iterate through all of the sale product pages on the site
		for ($i=1; $i <= $pages; $i++) {
			
			// gather the products from the DOM
			$products = $crawler->filter('ul.products-grid > li.item')->each(function ($node, $i) {

				// create a new crawler for the product html and create a new entity for it
				$product = new Crawler($node);
				$entity = new Product();

				// populate the new product entity
				$entity->setName(ucwords(htmlspecialchars_decode($product->filter('h2.product-name')->text().' '.$product->filter('.shortdesc')->text(), ENT_QUOTES)));
				$entity->setUrl($product->filter('a')->attr('href'));
				$entity->setThumbnail(str_replace('/just-in', '', $product->filter('img.front2')->attr('src')));
				$entity->setImage($entity->getThumbnail());
				$entity->setNew(true);
				
				// strip the pid from the product url
				$pid = explode('-', $product->filter('.price-box > .regular-price')->attr('id'));
				$entity->setPid(end($pid));

				// parse the price
				$now = str_replace(',', '', $product->filter('.price-box > .regular-price > .price')->text());
				$now = preg_replace('/[^0-9,.]/', '', $now);
				$entity->setNow($now);
				$entity->setWas(0);	
				$entity->setSale(null);

				return $entity;
			});
			

			// begin persisting the products until we reach products that we've already covered
			try
			{
				foreach ($products as $product) {
					// check whether the new product is already in the db, if not then add to shop and persist
					if (!in_array($product->getPid(), $existing)) {
						// sometimes the crawler will result in an empty entity, so don't persist
						if ($product->getName()) {
							$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
							$em->persist($product);
							$results++;
						}	
					} else {
						// escape from looping the pages once we find an item that's already added
						throw new ProductAlreadyParsedException();
					}
				}
				$em->flush();
			}
			catch (ProductAlreadyParsedException $e) 
			{
				// flush the final new products before killing the loop
				$em->flush();
				break;
			}

			// get the next page of results if we're not on the last page
			if ($i < $pages) {
				$html = $parser->execHtml('http://www.london-boutiques.com/just-in?limit=120&p='.($i+1));
				$crawler = new Crawler($html);	
			} else {
				// escape the loop if we've processed the last page
				break;
			}
		}
		return $results;
	}

	public function updateMytheresaLatest($em, $shop, $existing, $parser)
	{
		$results = 0;

		// crawl through the indivudal categories
		$categories = array(
			'8478' => 1, 
			'8476' => 2,
			'8477' => 3,
			'8475' => 4,
		);	

		// crawl through each category
		foreach ($categories as $categoryUrl => $categoryInternal) {

			// parse the first page and convert the html into a crawler object
			$html = $parser->execHtml('http://www.mytheresa.com/en-gb/new-arrivals/what-s-new-this-week-1.html?category='.$categoryUrl);
			$crawler = new Crawler($html);

			// count how many pages need crawling
			$pages = 1;

			// iterate continually until we break the loop
			while (true) {
				
				// gather the products from the DOM
				$products = $crawler->filter('ul.products-grid > li.item')->each(function ($node, $i) {

					// create a new crawler for the product html and create a new entity for it
					$product = new Crawler($node);
					$entity = new Product();

					// populate the new product entity
					$entity->setName(ucwords(htmlspecialchars_decode($product->filter('h2.designer-name')->text().' '.strtolower($product->filter('h3.product-name')->text()), ENT_QUOTES)));
					$entity->setUrl($product->filter('a')->attr('href'));
					$entity->setThumbnail($product->filter('img.image1st')->attr('src'));
					$entity->setImage($entity->getThumbnail());
					$entity->setNew(true);
					
					// strip the pid from the product url
					$pid = explode('-', $product->filter('.price-box > .regular-price')->attr('id'));
					$entity->setPid(end($pid));

					// parse the price
					$now = str_replace(',', '', $product->filter('.price-box > .regular-price > .price')->text());
					$now = preg_replace('/[^0-9,.]/', '', $now);
					$entity->setNow($now);
					$entity->setWas(null);
					$entity->setSale(null);

					return $entity;
				});
				

				// begin persisting the products until we reach products that we've already covered
				try
				{
					foreach ($products as $product) {
						// check whether the new product is already in the db, if not then add to shop and persist
						if (!in_array($product->getPid(), $existing)) {
							// sometimes the crawler will result in an empty entity, so don't persist
							if ($product->getName()) {
								$product->setShop($em->getReference('ThreadAndMirrorProductsBundle:Shop', $shop));
								$product->setCategory($categoryInternal);
								$em->persist($product);
								$results++;
							}	
						} else {
							// escape from looping the pages once we find an item that's already added
							throw new ProductAlreadyParsedException();
						}
					}
					$em->flush();
				}
				catch (ProductAlreadyParsedException $e) 
				{
					// flush the final new products before killing the loop
					$em->flush();
					break;
				}

				// get the next page of results if we're not on the last page (ie. if the next button doesn't exist)
				try 
				{
					$next = $crawler->filter('.i-next')->text();
					$pages++;
					$html = $parser->execHtml('http://www.mytheresa.com/en-gb/new-arrivals/what-s-new-this-week-1.html?category='.$categoryUrl.'&p='.$pages);
					$crawler = new Crawler($html);	
				} 
				catch (\InvalidArgumentException $e)
				{
					// escape the loop if we've processed the last page
					break;
				}
			}
		}
		return $results;
	}


	///////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////// SINGLE PRODUCT PARSERS ////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////

	public function updateNetaporterProduct($product, $parser, $em)
	{
		$date = new \DateTime();

		// parse the product page and get all the stuff we need
		$html = $parser->execHtml($product->getUrl());
		$crawler = new Crawler($html);
		
		// get the full res image of the product, if we don't have one
		!$product->getImage() and $product->setImage($product->getThumbnail());

		// check for the out of stock message
		try 
		{
			// look for the out of stock message
			$message = $crawler->filter('#button-holder > .button > .message')->text();
			$available = false;
		}
		catch (\InvalidArgumentException $e) 
		{
			// if the node list is empty then the item is available
			$available = true;

			// update the price
			$now = str_replace(',', '', $crawler->filter('div#price')->text());
			$now = preg_replace('/[^0-9,.]/', '', $now);

			if ($now != $product->getNow()) {
				$product->setUpdated($date);
				$product->setNow($now);
			}
		}
		
		// marked as updated if the availability has changed
		if ((!$available && $product->getAvailable() == true) || ($available && $product->getAvailable() == false)) {
			$product->setAvailable($available);
			$product->setUpdated($date);

			// create a back in stock alert if the product was out of stock previously
			if ($available) {
				$alert = new AlertBackInStock($product);
				$em->persist($alert);
			}
		}

		return $product;
	}

	public function updateMatchesProduct($product, $parser, $em)
	{
		$date = new \DateTime();

		// parse the product page and get all the stuff we need
		$html = $parser->execHtml($product->getUrl());
		$crawler = new Crawler($html);

		// get the full res image of the product, if we don't have one
		!$product->getImage() and $product->setImage($product->getThumbnail());

		// wrap the whole parser assume any uncaught node errors are an expired product
		try
		{
			// check for whether they're out of stock first, as the product details won't show
			try 
			{
				// look for the out of stock message
				$message = $crawler->filter('div#content > h2.heading')->text();
				$available = false;
			}
			catch (\InvalidArgumentException $e) 
			{
				// if the node list is empty then the item is available
				$available = true;
			}
			
			// marked as updated if the availability has changed
			if ((!$available && $product->getAvailable() == true) || ($available && $product->getAvailable() == false)) {
				$product->setAvailable($available);
				$product->setUpdated($date);

				// create a back in stock alert if the product was out of stock previously
				if ($available) {
					$alert = new AlertBackInStock($product);
					$em->persist($alert);
				}
			}

			// only perform the check if stock is available, otherwise there's no data to parse
			if ($available) {

				// check for the sale price field to see if the item is on sale or not 
				try 
				{
					//strip text from the price strings
					$was = explode('£', $crawler->filter('.product-details .pricing div.price > span.full')->text());
					$was = str_replace(',', '', end($was));
					$now = explode('£', $crawler->filter('.product-details .pricing div.price > span.sale')->text());
					$now = str_replace(',', '', end($now));
					$sale = true;
				}
				catch (\InvalidArgumentException $e) 
				{
					// if the node list is empty then sale prices don't exist
					$now = str_replace(',', '', $crawler->filter('.product-details .pricing .price')->text());
					$was = 0;
					$now = preg_replace('/[^0-9,.]/', '', $now);
					$sale = false;
				}
				
				if ($now != $product->getNow()) {
					$product->setUpdated($date);
					$product->setNow($now);
				}
				if ($was != $product->getWas()) {
					$product->setUpdated($date);
					$product->setWas($was);
				}
				if ($sale != $product->getSale()) {
					$product->setUpdated($date);
					$product->setSale($sale);
				}
			}
		}
		catch (\InvalidArgumentException $e)
		{
			$product->setAvailable(false);
			$product->setUpdated($date);
		}

		return $product;
	}

	public function updateBrownsProduct($product, $parser, $em)
	{
		$date = new \DateTime();

		// parse the product page and get all the stuff we need
		$html = $parser->execHtml($product->getUrl());
		$crawler = new Crawler($html);

		// get the full res image of the product, if we don't have one
		!$product->getImage() and $product->setImage($product->getThumbnail());

		// tidy up old products with funny characters in the name
		$product->setName(htmlspecialchars_decode($product->getName()));

		// dunno what an out of stock looks like so catch any empty nodes
		try
		{
			// parse the prices
			$was = str_replace(',', '', $crawler->filter('span.p_pr > span.currency-value')->text());
			$was = preg_replace('/[^0-9,.]/', '', $was);
			$now = str_replace(',', '', $crawler->filter('span.p_sale > span.currency-value')->text());
			$now = preg_replace('/[^0-9,.]/', '', $now);

			if ($now != $product->getNow()) {
				$product->setUpdated($date);
				$product->setNow($now);
			}
			if ($was != $product->getWas()) {
				$product->setUpdated($date);
				$product->setWas($was);
			}
		}
		catch (\InvalidArgumentException $e) 
		{
			// $now = str_replace(',', '', $product->filter('.currUpdate > span.currency-value')->text());
			// $now = preg_replace('/[^0-9,.]/', '', $now);
			// $entity->setNow($now);
			// $entity->setWas(0);
			// $entity->setSale(null);
		}
			
		return $product;
	}
}