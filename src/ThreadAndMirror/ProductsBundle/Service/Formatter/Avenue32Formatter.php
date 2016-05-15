<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class Avenue32Formatter extends AbstractFormatter
{
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
			->format($product->getPortraits())
			->replace('sw=180&sh=220', 'sw=556&sh=680')
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