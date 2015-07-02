<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class CultBeautyFormatter extends AbstractFormatter
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
			->sheer('By', false)
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

	protected function cleanupCrawledPid(Product $product)
	{
		$result = $this
			->format($product->getPid())
			->sheer('product_id/')
			->sheer('/uenc', false)
			->result();

		$product->setPid($result);
	}

	protected function cleanupCrawledDescription(Product $product) 
	{
		$result = $this
			->format($product->getDescription())
			->result();

		$product->setDescription($result);
	}

	protected function cleanupCrawledImages(Product $product) 
	{
		$result = $this
			->format($product->getImages())
			->replace('thumbnail/78x/', 'image/390x490/')
			->result();

		$product->setImages($result);
	}

	protected function cleanupCrawledPortraits(Product $product) 
	{ 
		$result = $this
			->format($product->getPortraits())
			->replace('thumbnail/78x/', 'image/200x240/')
			->result();

		$product->setPortraits($result);
	}

	protected function cleanupCrawledThumbnails(Product $product) 
	{
		$result = $this
			->format($product->getThumbnails())
			->result();

		$product->setThumbnails($result);
	}
}