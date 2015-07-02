<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class UrbanRetreatBeautiqueFormatter extends AbstractFormatter
{
	protected function cleanupCrawledUrl(Product $product)
	{
		$result = $this
			->format($product->getUrl())
			->result();

		$product->setUrl($result);
	}

	protected function cleanupCrawledName(Product $product)
	{
		$result = $this
			->format($product->getName())
			->trim()
			->result();

		$product->setName($result);
	}

	protected function cleanupCrawledBrand(Product $product)
	{
		$result = $this
			->format($product->getBrandName())
			->name()
			->result();

		$product->setBrandName($result);
	}

	protected function cleanupCrawledDescription(Product $product)
	{
		$result = $this
			->format($product->getDescription())
			->implode(' ')
			->result();

		$product->setDescription($result);
	}

	protected function cleanupCrawledImages(Product $product)
	{
		$result = $this
			->format($product->getImages())
			->prepend('http://www.urbanretreat.co.uk')
			->remove('&path=/product_images/')
			->result();

		$product->setImages($result);
	}

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