<?php

namespace ThreadAndMirror\ProductsBundle\Service;

use Symfony\Component\DomCrawler\Crawler,
	Symfony\Bridge\Monolog\Logger,
	Doctrine\ORM\EntityManager,
	ThreadAndMirror\ProductsBundle\Entity\Product,
	ThreadAndMirror\AlertBundle\Entity\AlertBackInStock,
	ThreadAndMirror\AlertBundle\Entity\AlertNowOnSale,
	ThreadAndMirror\AlertBundle\Entity\AlertFurtherPriceChange;

class Linkshare
{
	// the entity manager
	protected $em;

	// the logger service
	protected $logger;

	// the shop(s)
	protected $shops = array();

	// the product parser service
	protected $productParser;

	// the xml data
	protected $xml;

	public function __construct(EntityManager $em, $productParser)
	{
		$this->em = $em;
		$this->productParser = $productParser;
		$this->logger = new Logger('threadandmirror');

		return $this;
	}

	/**
	 * Process the requested feed file, both adding and updating
	 */
	public function parseProducts($slug, $type='delta')
	{
		// get the shop
		$this->shop = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->findOneBySlug($slug);

		// skip parsing if we're doing a delta and we've already parsed the latest feed version
		if ($type == 'delta' && $this->shop->getFeedParsed() && $this->shop->getFeedModified() < $this->shop->getFeedParsed()) {
			echo 'No new feed to parse'.PHP_EOL;
			return 0;
		}

		// load the relevant xml file
		if ($type == 'delta') {
			$this->loadXmlFile('http://www.threadandmirror.com/xml/'.$this->shop->getAffiliateId().'_3146542_mp_delta.xml');
		} else {
			$this->loadXmlFile('http://www.threadandmirror.com/xml/'.$this->shop->getAffiliateId().'_3146542_mp.xml');
		}

		// load the shop's parser in the event that we need custom functions, such as affiliate link generation
        $class = 'ThreadAndMirror\\ProductsBundle\\Parser\\'.ucfirst(str_replace('-', '', $slug)).'Parser';
        $parser = new $class($this->productParser);

		// get pids for all the products we have already
		$existingPids = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->getExistingProductIds($this->shop->getId());

		$added = 0;
		$updated = 0;

		// parse the products
		foreach ($this->xml->product as $product) {

			// construct the product entity from the data
			$pid = $product['product_id'];

			// ignore any male products
			if ($product->attributeClass->Gender != 'Male') {

				if (!in_array($pid, $existingPids)) {

					$entity = new Product();
					$entity->setPid($pid);
					$entity->setShop($this->shop);

					// set the type of product we're handling
					$entity->setAttire($this->shop->getAttire());
					$entity->setBeauty($this->shop->getBeauty());

					// parse the true url from the affiliate url
					$url = explode('murl=', $product->URL->product);
					$url = end($url);
					$url = rawurldecode($url);
					$entity->setUrl($url);

					$entity->setAffiliateUrl($product->URL->product);
					$entity->setImage($product->URL->productImage);

					$thumbnail = $parser->getThumbnailFromImage($product->URL->productImage);
					$entity->setThumbnail($thumbnail);

					$entity->setName(ucwords(strtolower($product['manufacturer_name'])).' '.$product['name']);
					$entity->setDesigner(ucwords(strtolower($product['manufacturer_name'])));
					$entity->setType($product['name']);
					$entity->setDescription('<p>'.$product->description->short.'</p>');
					$entity->setCategory($this->convertCategory($product->category->primary));

					if ($product->price->sale == $product->price->retail) {
						$entity->setSale(null);
						$entity->setWas(0.00);
						$entity->setNow($product->price->retail);
						$entity->setNew(true);
					} else {
						$entity->setSale(new \DateTime());
						$entity->setWas($product->price->sale);
						$entity->setNow($product->price->retail);
					}

					// seo bits
					$entity->setMetaDescription($product->description->short);

					$keywords = explode('~~', $product->keywords);
					$combinedKeywords = '';

					foreach ($keywords as $keyword) {
						$combinedKeywords .= $keyword.',';
					}

					$entity->setMetaKeywords($combinedKeywords);

					// save
					$this->em->persist($entity);
					$added++;

					// echo for debug assist
					echo $pid.' added.'.PHP_EOL;

				} else {

					// get the existing product
					$existingProduct = $this->em->getRepository('ThreadAndMirrorProductsBundle:Product')->findOneBy(array('pid' => $pid, 'shop' => $this->shop));

					// parse the prices
					$sale = floatval($product->price->sale);
					$retail = floatval($product->price->retail);

					if ($sale == $retail || $sale == 0 || !$sale) {
						// add the current price
						$existingProduct->setSale(null);
						$existingProduct->setWas(0.00);
						$existingProduct->setNow($retail);
					} else {
						// update and alert if the product is now on sale
						if (!$existingProduct->getSale()) {
							$existingProduct->setSale(new \DateTime());
							$existingProduct->setUpdated(new \DateTime());
							$alert = new AlertNowOnSale($existingProduct);
							$this->em->persist($alert);

						// update and alert if the sale price has changed further
						} else if ($sale != $existingProduct->getNow()) {
							$existingProduct->setUpdated(new \DateTime());
							$previousPrice = $existingProduct->getNow();
							$existingProduct->setNow($sale);
							$alert = new AlertFurtherPriceChange($existingProduct, $previousPrice);
							$this->em->persist($alert);
						}

						$existingProduct->setWas($retail);
						$existingProduct->setNow($sale);
					}

					// save
					$this->em->persist($existingProduct);
					$updated++;

					// echo for debug assist
					echo $pid.' updated.'.PHP_EOL;
				}
			}

			// flush every 100 products to avoid them building up in memory
			($added + $updated % 100) === 0 and $this->em->flush();
		}

		$this->shop->setFeedParsed(new \DateTime());
		$this->em->persist($this->shop);
		$this->em->flush();

		return $added;
	}

	/**
	 * Converts a string into a relevant T&M category ID, returning null if no matches could be found
	 */
	protected function convertCategory($category)
	{
		$category = strtolower($category);
		$category = html_entity_decode($category);

		// Clothing
		if (stristr($category, 'clothing')) {
			return 1;
		}
		if (stristr($category, 'dresses')) {
			return 1;
		}
		if (stristr($category, 'skirts')) {
			return 1;
		}
		if (stristr($category, 'tops')) {
			return 1;
		}
		if (stristr($category, 'trousers')) {
			return 1;
		}
		if (stristr($category, 'leggings')) {
			return 1;
		}
		if (stristr($category, 'jeans')) {
			return 1;
		}
		if (stristr($category, 'denim')) {
			return 1;
		}
		if (stristr($category, 'jackets')) {
			return 1;
		}
		if (stristr($category, 'coats')) {
			return 1;
		}
		if (stristr($category, 'suits')) {
			return 1;
		}
		if (stristr($category, 'knitwear')) {
			return 1;
		}
		if (stristr($category, 'blouses')) {
			return 1;
		}
		if (stristr($category, 'shirts')) {
			return 1;
		}

		// Shoes
		if (stristr($category, 'shoes')) {
			return 2;
		}
		if (stristr($category, 'boots')) {
			return 2;
		}
		if (stristr($category, 'high heels')) {
			return 2;
		}
		if (stristr($category, 'flats')) {
			return 2;
		}

		// Bags
		if (stristr($category, 'bags')) {
			return 3;
		}
		if (stristr($category, 'totes')) {
			return 3;
		}
		if (stristr($category, 'wallets')) {
			return 3;
		}
		if (stristr($category, 'purses')) {
			return 3;
		}
		if (stristr($category, 'wallet')) {
			return 3;
		}
		if (stristr($category, 'purse')) {
			return 3;
		}
		if (stristr($category, 'cross body')) {
			return 3;
		}

		// Accessories
		if (stristr($category, 'accesories')) {
			return 4;
		}
		if (stristr($category, 'belts')) {
			return 4;
		}
		if (stristr($category, 'sunglasses')) {
			return 4;
		}
		if (stristr($category, 'scarves')) {
			return 4;
		}

		// Jewellery
		if (stristr($category, 'jewellery')) {
			return 5;
		}

		// Underwear
		if (stristr($category, 'underwear')) {
			return 6;
		}
		if (stristr($category, 'lingerie')) {
			return 6;
		}
		if (stristr($category, 'hosiery')) {
			return 6;
		}

		// Swimwear
		if (stristr($category, 'swimwear')) {
			return 7;
		}
		if (stristr($category, 'bikinis')) {
			return 7;
		}
		if (stristr($category, 'swimsuits')) {
			return 7;
		}
		if (stristr($category, 'beachwear')) {
			return 7;
		}

		// Vintage
		if (stristr($category, 'vintage')) {
			return 8;
		}

		// Body
		if (stristr($category, 'body')) {
			return 20;
		}

		// Skincare
		if (stristr($category, 'skin care')) {
			return 21;
		}
		if (stristr($category, 'skincare')) {
			return 21;
		}
		if (stristr($category, 'cream')) {
			return 21;
		}
		if (stristr($category, 'masks')) {
			return 21;
		}

		// Haircare
		if (stristr($category, 'hair care')) {
			return 22;
		}
		if (stristr($category, 'haircare')) {
			return 22;
		}

		// Makeup
		if (stristr($category, 'make up')) {
			return 23;
		}
		if (stristr($category, 'makeup')) {
			return 23;
		}
		if (stristr($category, 'foundation')) {
			return 23;
		}
		if (stristr($category, 'lipstick')) {
			return 23;
		}
		if (stristr($category, 'primer')) {
			return 23;
		}

		// Wellbeing
		if (stristr($category, 'wellbeing')) {
			return 24;
		}

		// Fragrance
		if (stristr($category, 'fragrance')) {
			return 25;
		}

		// Gifts
		if (stristr($category, 'gifts')) {
			return 26;
		}
		if (stristr($category, 'books')) {
			return 26;
		}

		// Travel
		if (stristr($category, 'sun')) {
			return 27;
		}
		if (stristr($category, 'travel')) {
			return 27;
		}
		if (stristr($category, 'holiday prep')) {
			return 27;
		}

		// Brushes and Lash Curlers
		if (stristr($category, 'brushes')) {
			return 28;
		}
		if (stristr($category, 'curlers')) {
			return 28;
		}



		return null;
	}

	// protected $availableCategories = array(
	// 	'Clothing'		=> 1,
	// 	'Shoes'			=> 2,
	// 	'Bags'			=> 3,
	// 	'Accessories' 	=> 4,
	// 	'Jewellery' 	=> 5,
	// );
	

	protected function loadXmlFile($path)
	{
		$this->xml = simplexml_load_file($path);
	}
}
