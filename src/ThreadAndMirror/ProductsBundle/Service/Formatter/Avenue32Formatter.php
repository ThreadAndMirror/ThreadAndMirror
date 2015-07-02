<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class Avenue32Formatter extends AbstractFormatter
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
			->sheer('id=')
			->result();

		$product->setPid($result);
	}

	protected function cleanupCrawledDescription(Product $product) 
	{
		$result = $this
			->format($product->getDescription())
			->implode()
			->append('.')
			->result();

		$product->setDescription($result);
	}

	protected function cleanupCrawledNow(Product $product)
	{
		$result = $this
			->format($product->getNow())
			->sheer('(', false)
			->currency()
			->result();

		$product->setNow($result);
	}

	protected function cleanupCrawledWas(Product $product)
	{
		$result = $this
			->format($product->getWas())
			->sheer('(', false)
			->currency()
			->result();

		$product->setWas($result);
	}

	protected function cleanupCrawledImages(Product $product) 
	{
		$result = $this
			->format($product->getThumbnails())
			->replace('/230/100/', '/230/900/')
			->result();

		$product->setImages($result);
	}

	protected function cleanupCrawledPortraits(Product $product) 
	{ 
		$result = $this
			->format($product->getThumbnails())
			->replace('/230/100/', '/230/230/')
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

	protected function cleanupCrawledAvailableSizes(Product $product) 
	{
		$sizes = $product->getAvailableSizes();

		foreach ($sizes as $key => $size) {
			if (stristr($size, 'Waiting') || stristr($size, 'CHOOSE SIZE')) {
				unset($sizes[$key]);
			}
		}
		
		$product->setAvailableSizes($sizes);
	}

	protected function cleanupCrawledStockedSizes(Product $product) 
	{ 
		$sizes = $product->getStockedSizes();

		foreach ($sizes as $key => $size) {
			if (stristr($size, 'Waiting')) {
				$sizes[$key] = $this->format($size)->sheer(' »', false)->trim()->result();
			}
			if (stristr($size, 'CHOOSE SIZE')) {
				unset($sizes[$key]);
			}
		}

		$product->setStockedSizes($sizes);
	}

	protected function cleanupCrawledStyleWith(Product $product) 
	{ 

	}
}