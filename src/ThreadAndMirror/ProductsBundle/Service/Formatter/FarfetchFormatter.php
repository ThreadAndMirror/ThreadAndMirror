<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class FarfetchFormatter extends AbstractFormatter
{
	protected function cleanupCrawledUrl(Product $product) 
	{ 

	}

	protected function cleanupCrawledName(Product $product) 
	{ 
		$result = $this->format($product->getName())->name()->result();
		$product->setName($result);
	}

	protected function cleanupCrawledBrand(Product $product) 
	{ 
		
	}

	protected function cleanupCrawledPid(Product $product) 
	{ 

	}

	protected function cleanupCrawledDescription(Product $product) 
	{ 

	}
	
	protected function cleanupCrawledImages(Product $product) 
	{ 
		$result = $this->format($product->getImages())->replace('_70.jpg', '_480.jpg')->result();
		$product->setImages($result);
	}

	protected function cleanupCrawledPortraits(Product $product) 
	{ 
		$result = $this->format($product->getPortraits())->replace('_70.jpg', '_240.jpg')->result();
		$product->setPortraits($result);
	}

	protected function cleanupCrawledThumbnails(Product $product) 
	{ 
		
	}

	protected function cleanupCrawledAvailableSizes(Product $product) 
	{
		
	}

	protected function cleanupCrawledStockedSizes(Product $product) 
	{ 
		
	}

	protected function cleanupCrawledStyleWith(Product $product) 
	{ 

	}
}