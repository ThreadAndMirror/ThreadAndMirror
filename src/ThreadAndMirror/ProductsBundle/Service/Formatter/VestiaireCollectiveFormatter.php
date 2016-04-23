<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class VestiaireCollectiveFormatter extends AbstractFormatter
{
	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledImages(Product $product)
	{
		$result = $this
			->format($product->getThumbnails())
			->replace('?auto=format&fm=pjpg&w=50&dpr=2', '')
			->result();

		$product->setImages($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledPortraits(Product $product)
	{
		$result = $this
			->format($product->getThumbnails())
			->replace('w=50', 'w=224')
			->result();

		$product->setPortraits($result);
	}
}