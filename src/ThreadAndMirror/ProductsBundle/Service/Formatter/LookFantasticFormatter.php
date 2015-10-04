<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class LookFantasticFormatter extends AbstractFormatter
{
	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedImages(Product $product) 
	{ 
		$result = $this
			->format($product->getThumbnail())
			->replace('img/300/300', 'img/600/600')
			->result();

		$product->setImages([$result]);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedPortraits(Product $product) 
	{ 
		$result = $this
			->format($product->getThumbnail())
			->result();

		$product->setPortraits([$result]);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedThumbnails(Product $product) 
	{ 
		$result = $this
			->format($product->getThumbnail())
			->replace('img/300/300', 'img/130/130')
			->result();

		$product->setThumbnails([$result]);
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
	protected function cleanupCrawledWas(Product $product)
	{
		if ($product->getWas() != null) {

			$result = $this
				->format($product->getWas())
				->currency()
				->result();
			$product->setWas($product->getNow() + (($result / 100) * $product->getNow()));
		}
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
			->replace('300/300', '600/600')
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
			->replace('300/300', '180/180')
			->result();

		$product->setThumbnails($result);
	}
}