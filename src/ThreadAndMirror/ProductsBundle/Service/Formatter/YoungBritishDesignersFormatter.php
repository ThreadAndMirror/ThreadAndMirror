<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class YoungBritishDesignersFormatter extends AbstractFormatter
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
	protected function cleanupFeedName(Product $product)
	{
		$result = $this->format($product->getName())
			->sheer(' by ', false)
			->trim()
			->result();

		$product->setName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedPortraits(Product $product)
	{
		$result = $this
			->format($product->getPortraits())
			->replace('_raw', '_main')
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
			->replace('_raw', '_main')
			->result();

		$product->setThumbnails($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledUrl(Product $product) 
	{
		$result = $this->format($product->getUrl())
			->prepend('http://www.youngbritishdesigners.com')
			->result();

		$product->setUrl($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledBrand(Product $product) 
	{ 
		$result = $this->format($product->getBrandName())
			->name()
			->result();

		$product->setBrandName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledPid(Product $product)
	{
		$result = $this->format($product->getPid())
			->sheer('?order=')
			->sheer('&quant', false)
			->result();

		$product->setPid($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledImages(Product $product) 
	{
		$result = $this->format($product->getThumbnails())
			->prepend('http://www.youngbritishdesigners.com')
			->replace('_thumb', '_485')
			->result();

		$product->setImages($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledPortraits(Product $product) 
	{ 
		$result = $this->format($product->getThumbnails())
			->prepend('http://www.youngbritishdesigners.com')
			->replace('_thumb', '_main')
			->result();

		$product->setPortraits($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledThumbnails(Product $product) 
	{
		$result = $this->format($product->getThumbnails())
			->prepend('http://www.youngbritishdesigners.com')
			->result();

		$product->setThumbnails($result);
	}
}