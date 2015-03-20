<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class TopshopFormatter extends AbstractFormatter
{
	protected function cleanupFeedName(Product $product) 
	{ 
		$result = $this->format($product->getName())
			->sheer(' - ')
			->replace('**', '')
			->replace('Womens ', '')
			->replace('MATERNITY', 'Maternity')
			->result();

		$product->setName($result);
	}

	protected function cleanupFeedImages(Product $product) 
	{ 
		$result = $this->format($product->getImages())
			->replace('_normal.jpg', '_large.jpg')
			->result();

		$product->setImages($result);
	}

	protected function cleanupFeedPortraits(Product $product) 
	{ 
		$result = $this->format($product->getImages())
			->replace('_large.jpg', '_normal.jpg')
			->result();

		$product->setPortraits($result);
	}

	protected function cleanupFeedThumbnails(Product $product) 
	{ 
		$result = $this->format($product->getImagess())
			->replace('_large.jpg', '_small.jpg')
			->result();

		$product->setThumbnails($result);
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
		if (is_array($product->getStockedSizes())) {
			$product->setAvailableSizes(array_merge($product->getAvailableSizes(), $product->getStockedSizes()));
		} else {
			$product->getStockedSizes(array());
		}
	}

	protected function cleanupCrawledStockedSizes(Product $product) 
	{ 

	}

	protected function cleanupCrawledStyleWith(Product $product) 
	{ 

	}
}