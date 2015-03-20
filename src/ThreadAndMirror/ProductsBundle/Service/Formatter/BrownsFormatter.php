<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class BrownsFormatter extends AbstractFormatter
{
	/**
	 * Post-processing for feed product creation
	 *
	 * @param  Product 		$product 	The product to cleanup
	 */
	public function cleanupFeedProduct(Product $product) 
	{
		
	}

	protected function cleanupCrawledName(Product $product) 
	{ 

	}

	protected function cleanupCrawledBrand(Product $product) 
	{ 
		$result = $this->format($product->getBrandName())->name()->result();
		$product->setBrandName($result);
	}

	protected function cleanupCrawledPid(Product $product) 
	{ 

	}

	protected function cleanupCrawledDescription(Product $product) 
	{ 
		$result = $this->format($product->getDescription())->trim()->result();
		$product->setDescription($result);
	}

	protected function cleanupCrawledImages(Product $product) 
	{ 
		$result = $this->format($product->getImages())->replace('160x198', '670x830')->result();
		$product->setImages($result);
	}

	protected function cleanupCrawledPortraits(Product $product) 
	{ 
		$result = $this->format($product->getPortraits())->replace('160x198', '338x410')->result();
		$product->setPortraits($result);
	}

	protected function cleanupCrawledThumbnails(Product $product) 
	{ 

	}

	protected function cleanupCrawledAvailableSizes(Product $product) 
	{
		$product->setAvailableSizes(array_merge($product->getAvailableSizes(), $product->getStockedSizes()));
	}

	protected function cleanupCrawledStockedSizes(Product $product) 
	{ 

	}

	protected function cleanupCrawledStyleWith(Product $product) 
	{ 

	}
}