<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class YooxFormatter extends AbstractFormatter
{
	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedName(Product $product)
	{
		$result = $this
			->format($product->getName())
			->sheerSpecial('caps')
			->sheer(' WOMEN on ', false)
			->sheer(' UNISEX on ', false)
			->sheer('s', false)
			->sheer('es', false)
			->result();

		$product->setName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedBrand(Product $product)
	{
		$result = $this
			->format($product->getBrandName())
			->name()
			->result();

		$product->setBrandName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedDescription(Product $product)
	{
		$result = $this
			->format($product->getDescription())
			->sheer(' * ', false)->append('.')
			->result();

		// Yoox descriptions are pretty naff, so ignore
		$product->setDescription('');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedPortraits(Product $product)
	{
		$result = $this
			->format($product->getImages())
			->replace('_12_', '_20_')
			->result();

		$product->setPortraits($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedThumbnails(Product $product)
	{
		$result = $this
			->format($product->getImages())
			->replace('_12_', '_20_')
			->result();

		$product->setThumbnails($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledName(Product $product)
	{
		$result = $this
			->format($product->getName())
			->trim()
			->result();

		$product->setName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledBrand(Product $product)
	{
		$result = $this
			->format($product->getBrandName())
			->name()
			->result();

		$product->setBrandName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledPid(Product $product)
	{
		$result = $this
			->format($product->getPid())
			->result();

		$product->setPid($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledDescription(Product $product)
	{
		$result = $this
			->format($product->getDescription())
			->trim()
			->result();

		$product->setDescription($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledImages(Product $product)
	{
		$result = $this
			->format($product->getImages())
			->replace('9_f', '12_f')
			->result();

		$product->setImages($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledPortraits(Product $product)
	{

		$result = $this
			->format($product->getPortraits())
			->replace('9_f', '20_f')
			->result();

		$product->setPortraits($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledThumbnails(Product $product)
	{
		$result = $this
			->format($product->getThumbnails())
			->result();

		$product->setThumbnails($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledAvailableSizes(Product $product)
	{
		$result = $this
			->format($product->getAvailableSizes())
			->trim()
			->result();

		$product->setAvailableSizes($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledStockedSizes(Product $product)
	{
		$result = $this
			->format($product->getStockedSizes())
			->trim()
			->result();

		$product->setStockedSizes($result);
	}
}