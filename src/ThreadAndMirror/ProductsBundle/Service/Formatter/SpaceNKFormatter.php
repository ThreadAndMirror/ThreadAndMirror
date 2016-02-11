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
	protected function cleanupCrawledUrl(Product $product)
	{
		$result = $this
			->format($product->getUrl())
			->result();

		$product->setUrl($result);
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
			->name()
			->result();

		$product->setBrandName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledDescription(Product $product)
	{
		$result = $this
			->format($product->getDescription())
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
			->replace('?sw=344&sh=344&sfrm=jpg', '')
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
			->replace('?sw=344&sh=344&sfrm=jpg', '')
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
			->replace('?sw=344&sh=344&sfrm=jpg', '')
			->result();

		$product->setThumbnails($result);
	}
}