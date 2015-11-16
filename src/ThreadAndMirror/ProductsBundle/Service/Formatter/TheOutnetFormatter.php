<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class TheOutnetFormatter extends AbstractFormatter
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
	protected function cleanupFeedCategory(Product $product)
	{
		$result = $this->format($product->getCategoryName())
			->sheer('~~')
			->sheer('~~')
			->sheer('~~')
			->sheer(' > ', false)
			->result();

		$product->setCategoryName($result);
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
			->prepend('http:')
			->replace('in_s', 'in_xl')
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
			->prepend('http:')
			->replace('in_s', 'in_sl')
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
			->prepend('http:')
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

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledStyleWith(Product $product)
	{

	}
}