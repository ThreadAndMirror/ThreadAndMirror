<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class LaneCrawfordFormatter extends AbstractFormatter
{
	protected function cleanupCrawledName(Product $product) 
	{ 

	}

	protected function cleanupCrawledBrand(Product $product) 
	{ 
		$result = $this
			->format($product->getBrandName())
			->name()
			->result();

		$product->setBrandName($result);
	}

	protected function cleanupCrawledCategory(Product $product)
	{
		$result = $this
			->format($product->getCategoryName())
			->result();

		$product->setCategoryName($result);
	}

	protected function cleanupCrawledPid(Product $product) 
	{ 

	}

	protected function cleanupCrawledDescription(Product $product) 
	{ 
		$result = $this
			->format($product->getDescription())
			->trim()
			->result();

		$product->setDescription($result);
	}

	protected function cleanupCrawledImages(Product $product) 
	{ 
		$result = $this
			->format($product->getImages())
			->replace('in_xs', 'in_xl')
			->result();

		$product->setImages($result);
	}

	protected function cleanupCrawledPortraits(Product $product) 
	{ 
		$result = $this
			->format($product->getPortraits())
			->replace('in_xs', 'in_m')
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