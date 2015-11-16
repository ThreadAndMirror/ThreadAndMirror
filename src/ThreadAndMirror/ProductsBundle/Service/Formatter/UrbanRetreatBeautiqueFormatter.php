<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class UrbanRetreatBeautiqueFormatter extends AbstractFormatter
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
	protected function cleanupFeedImages(Product $product)
	{
		$result = $this
			->format($product->getImages())
			->replace('width=300&height=300', 'width=600&height=600')
			->result();

		$product->setImages($result);
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
	protected function cleanupCrawledDescription(Product $product)
	{
		$result = $this
			->format($product->getDescription())
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
			->prepend('http://www.urbanretreat.co.uk')
			->remove('&path=/product_images/')
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
			->prepend('http://www.urbanretreat.co.uk')
			->replace('width=900&height=900', 'width=235&height=235')
			->remove('&path=/product_images/')
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
			->prepend('http://www.urbanretreat.co.uk')
			->replace('width=900&height=900', 'width=120&height=120')
			->remove('&path=/product_images/')
			->result();

		$product->setThumbnails($result);
	}
}