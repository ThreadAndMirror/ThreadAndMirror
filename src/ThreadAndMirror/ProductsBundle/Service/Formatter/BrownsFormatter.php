<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class BrownsFormatter extends AbstractFormatter
{
	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedUrl(Product $product)
	{
		$result = $this
			->format($product->getUrl())
			->sheer('murl=')
			->result();

		$product->setUrl(rawurldecode($result));
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedPid(Product $product)
	{
		$metaData = json_decode($product->getMetaData());

		$result = $this
			->format($metaData->sku)
			->result();

		$product->setPid($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedCategory(Product $product)
	{
		$result = $this->format($product->getCategoryName())
			->sheer('~~', false)
			->replace('Women\'s', '')
			->trim()
			->name()
			->result();

		$product->setCategoryName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedPortraits(Product $product)
	{

		$result = $this
			->format($product->getPortraits())
			->replace('670x830', '338x410')
			->result();

		$product->setPortraits($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedThumbnails(Product $product)
	{
		$result = $this
			->format($product->getThumbnails())
			->replace('670x830', '160x198')
			->result();

		$product->setThumbnails($result);
	}

	protected function cleanupCrawledBrand(Product $product) 
	{ 
		$result = $this
			->format($product->getBrandName())
			->name()
			->result();

		$product->setBrandName($result);
	}

	protected function cleanupCrawledPid(Product $product) 
	{
		$result = $this
			->format($product->getPid())
			->trim()
			->sheer('REF: ')
			->result();

		$product->setPid($result);
	}

	protected function cleanupCrawledDescription(Product $product) 
	{ 
		$result = $this
			->format($product->getDescription())
			->trim()
			->result();

		$product->setDescription($result);
	}

	protected function cleanupCrawledImages(Product $product) 
	{ 
		$result = $this
			->format($product->getImages())
			->replace('160x198', '670x830')
			->result();

		$product->setImages($result);
	}

	protected function cleanupCrawledPortraits(Product $product) 
	{ 
		$result = $this
			->format($product->getPortraits())
			->replace('160x198', '338x410')
			->result();

		$product->setPortraits($result);
	}

	protected function cleanupCrawledAvailableSizes(Product $product) 
	{
		$product->setAvailableSizes(array_merge($product->getAvailableSizes(), $product->getStockedSizes()));
	}
}