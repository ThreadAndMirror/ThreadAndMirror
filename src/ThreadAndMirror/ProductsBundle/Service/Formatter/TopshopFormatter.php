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
		$result = $this->format($product->getThumbnails())
			->replace('_large.jpg', '_thumb.jpg')
			->result();

		$product->setThumbnails($result);
	}

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
			->replace('_normal', '_large')
			->result();

		$product->setImages($result);
	}

	protected function cleanupCrawledPortraits(Product $product) 
	{ 
		$result = $this
			->format($product->getPortraits())
			->result();

		$product->setPortraits($result);
	}

	protected function cleanupCrawledThumbnails(Product $product) 
	{
		$result = $this
			->format($product->getThumbnails())
			->replace('_normal', '_thumb')
			->result();

		$product->setThumbnails($result);
	}

	protected function cleanupCrawledAvailableSizes(Product $product) 
	{
		$sizes = $product->getAvailableSizes();

		foreach ($sizes as $key => $size) {
			if (stristr($size, 'Select Size')) {
				unset($sizes[$key]);
			} else {
				$sizes[$key] = $this->format($size)->result();
			}
		}

		$product->setAvailableSizes($sizes);
	}

	protected function cleanupCrawledStockedSizes(Product $product) 
	{
		$sizes = $product->getStockedSizes();

		foreach ($sizes as $key => $size) {
			if (stristr($size, 'Select Size')) {
				unset($sizes[$key]);
			} else {
				$sizes[$key] = $this->format($size)->result();
			}
		}

		$product->setStockedSizes($sizes);
	}

	protected function cleanupCrawledStyleWith(Product $product) 
	{ 

	}
}