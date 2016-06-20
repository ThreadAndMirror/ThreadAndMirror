<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class SpaceNKFormatter extends AbstractFormatter
{
	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedCategory(Product $product)
	{
		$categoryName = $product->getCategoryName();

		if (empty($categoryName) || $categoryName === 'Default') {
			$product->setCategoryName('Uncategorised');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedBrand(Product $product)
	{
		$brandName = $product->getBrandName();

		if (empty($brandName)) {
			$product->setBrandName('Unbranded');
		}
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
	protected function cleanupCrawledImages(Product $product)
	{
		$result = $this
			->format($product->getImages())
			->sheer('?', false)
			->result();

		$product->setImages($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledPortraits(Product $product)
	{
		$result = $this
			->format($product->getImages())
			->append('?sw=300&sh=300')
			->result();

		$product->setPortraits($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledThumbnails(Product $product) 
	{
		$result = $this
			->format($product->getImages())
			->append('?sw=150&sh=150')
			->result();

		$product->setThumbnails($result);
	}
}