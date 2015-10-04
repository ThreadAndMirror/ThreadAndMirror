<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class JohnLewisFormatter extends AbstractFormatter
{
	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedImages(Product $product)
	{
		$result = $this
			->format($product->getImages())
			->replace('fash_product', 'prod_main')
			->result();

		$product->setImages($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedThumbnails(Product $product)
	{
		$result = $this
			->format($product->getThumbnails())
			->replace('prod_thmb', 'prod_grid3')
			->result();

		$product->setThumbnails($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedPortraits(Product $product)
	{
		$result = $this
			->format($product->getPortraits())
			->replace('prod_thmb', 'prod_grid3')
			->result();

		$product->setPortraits($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedMetaKeywords(Product $product)
	{
		// Nuke the weird characters
		$product->setMetaKeywords('');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledName(Product $product)
	{
		$result = $this
			->format($product->getName())
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
			->result();

		$product->setBrandName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledCategory(Product $product)
	{
		$result = $this
			->format($product->getCategoryName())
			->result();

		$product->setCategoryName($result);
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
			->implode(' ')
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
			->replace('$prod_main$', '$prod_grid3$')
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
			->replace('$prod_main$', '$prod_thmb2$')
			->result();

		$product->setThumbnails($result);
	}
}