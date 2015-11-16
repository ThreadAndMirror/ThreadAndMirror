<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class CultBeautyFormatter extends AbstractFormatter
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
			->replace(' ', '')
			->result();

		$product->setPid($result);
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
	protected function cleanupCrawledName(Product $product) 
	{
		$result = $this
			->format($product->getName())
			->sheer('By', false)
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
			->sheer('product_id/')
			->sheer('/uenc', false)
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
			->replace('thumbnail/78x/', 'image/390x490/')
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
			->replace('thumbnail/78x/', 'image/200x240/')
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
}