<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class EscentualFormatter extends AbstractFormatter
{
	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedImages(Product $product)
	{
		$result = $this
			->format($product->getImages())
			->result();

		$product->setImages($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedThumbnails(Product $product)
	{
		$result = $this
			->format($product->getImages())
			->result();

		$product->setThumbnails($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedPortraits(Product $product)
	{
		$result = $this
			->format($product->getImages())
			->result();

		$product->setPortraits($result);
	}
}