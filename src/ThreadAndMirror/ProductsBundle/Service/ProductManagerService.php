<?php

namespace ThreadAndMirror\ProductsBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAware;
use	ThreadAndMirror\ProductsBundle\Service\ProductParser;

class ProductManagerService extends ContainerAware
{
	/**
	 * @var EntityManager
	 */
	protected $em;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * Get a product from the url
	 *
	 * @param  string 		$url 		The url of the product page
	 * @return Product
	 */
	public function getProductFromUrl($url)
	{
		// Check which shop the url is from
		$shop = $this->em->getRepository('ThreadAndMirrorProductsBundle:Shop')->getShopFromUrl($url);

		if (!$shop) {
			return null;
		}

		if ($shop->isCrawlable()) {

			// Crawl the product
			$product = $this->container->get($shop->getUpdaterName())->createProductFromCrawl($url);
			
			if (!is_object($product)) {
				return null;
			}

		} else {
			// @todo old style crawling, remove when all have been updated

			// See if the parser class exists for the shop
			$class = 'ThreadAndMirror\\ProductsBundle\\Parser\\'.ucfirst(str_replace('-', '', $shop->getSlug())).'Parser';

			if (class_exists($class)) {
				$parser = new $class(new ProductParser($this->em));
			} else {
				return null;
			}

			// Load the parser service and attempt to create the product
			$product = $parser->create($url);

			if (!is_object($product)) {
				return null;
			}
		}

		$product->setShop($shop);

		return $product;
	}

	/**
	 * Update an existing product
	 */
	public function updateProduct($product)
	{
		return $this->getProductFromUrl($product->getUrl());
	}
}
