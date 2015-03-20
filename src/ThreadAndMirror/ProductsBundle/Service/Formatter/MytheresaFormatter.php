<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class MytheresaFormatter extends AbstractFormatter
{
	protected function cleanupCrawledUrl(Product $product) 
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
		$result = $this->format($product->getPid())->trim()->result();
		$product->setPid($result);
	}

	protected function cleanupCrawledDescription(Product $product) 
	{ 

	}
	
	protected function cleanupCrawledImages(Product $product) 
	{ 

	}

	protected function cleanupCrawledPortraits(Product $product) 
	{ 
		$result = $this->format($product->getPortraits())->replace('420x475', '230x260')->result();
		$product->setPortraits($result);
	}

	protected function cleanupCrawledThumbnails(Product $product) 
	{ 
		$result = $this->format($product->getThumbnails())->replace('420x475', '100x')->result();
		$product->setThumbnails($result);
	}

	protected function cleanupCrawledAvailableSizes(Product $product) 
	{
		$sizes = $product->getAvailableSizes();

		foreach ($sizes as $key => $size) {
			if (stristr($size, 'wishlist')) {
				unset($sizes[$key]);
			}
		}
		
		$product->setAvailableSizes($sizes);
	}

	protected function cleanupCrawledStockedSizes(Product $product) 
	{ 
		$sizes = $product->getStockedSizes();

		foreach ($sizes as $key => $size) {
			if (stristr($size, 'wishlist')) {
				$sizes[$key] = $this->format($size)->sheer('-', false)->trim()->result();
			}
		}

		$product->setStockedSizes($sizes);
	}

	protected function cleanupCrawledStyleWith(Product $product) 
	{ 

	}
}