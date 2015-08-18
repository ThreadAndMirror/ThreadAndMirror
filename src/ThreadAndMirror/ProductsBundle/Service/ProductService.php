<?php

namespace ThreadAndMirror\ProductsBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAware;
use ThreadAndMirror\ProductsBundle\Entity\Product;
use ThreadAndMirror\ProductsBundle\Repository\ProductRepository;
use	ThreadAndMirror\ProductsBundle\Service\ProductParser;

/**
 * Class ProductService
 * @package ThreadAndMirror\ProductsBundle
 *
 * Container aware, so we can pull in the multitude of updaters on dynamically
 */
class ProductService extends ContainerAware
{
	/** @var EntityManager */
	protected $em;

	/** @var ProductRepository */
	protected $productRepository;

	public function __construct(EntityManager $em, ProductRepository $productRepository)
	{
		$this->em                = $em;
		$this->productRepository = $productRepository;
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

		// Crawl the product
		if ($shop->isCrawlable()) {

			/** @var Product $product */
			$product = $this->container->get($shop->getUpdaterName())->createProductFromCrawl($url);

			if (!is_object($product)) {
				return null;
			}

			// See if a product already exists with that SKU and shop
			// @todo change this to use cache to find the uid and/or sku (both?)
			$existing = $this->productRepository->findOneBy(['shop' => $shop, 'pid' => $product->getPid()]);

			if ($existing !== null) {
				return $existing;
			}

			$product->setShop($shop);

			return $product;
		}

		return null;
	}

	/**
	 * Update an existing product
	 */
	public function updateProduct($product)
	{
		return $this->getProductFromUrl($product->getUrl());
	}
}
