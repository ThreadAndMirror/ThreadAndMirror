<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class FrenchConnectionFormatter extends AbstractFormatter
{
	public function cleanupFeedName(Product $product) 
	{
		// For some reason some shops stick the colour in the name...
		$result = $this
			->format($product->getName())
			->sheer(' - ')
			->result();

		$product->setName($result);
	}

	protected function cleanupFeedDescription(Product $product) 
	{ 
		$result = $this
			->format($product->getDescription())
			->sheer(' * ')->append('.')
			->result();

		$product->setDescription($result);
	}

	protected function cleanupCrawledUrl(Product $product) 
	{ 
		$result = $this
			->format($product->getUrl())
			->prepend('http://www.frenchconnection.com')
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
			->trim()
			->result();

		$product->setPid($result);
	}

	protected function cleanupCrawledDescription(Product $product) 
	{ 

	}

	
	protected function cleanupCrawledImages(Product $product) 
	{ 
		$result = $this
			->format($product->getImages())
			->sheer('?', false)
			->result();

		$product->setImages($result);
	}

	protected function cleanupCrawledPortraits(Product $product) 
	{ 
		$result = $this
			->format($product->getImages())
			->sheer('?', false)->replace('731/487', '384/263')
			->result();

		$product->setImages($result);
	}

	protected function cleanupCrawledThumbnails(Product $product) 
	{ 
		$result = $this
			->format($product->getImages())
			->sheer('?', false)->replace('731/487', '117/78')
			->result();

		$product->setImages($result);
	}

	protected function cleanupCrawledAvailableSizes(Product $product)
	{
		$sizes = $product->getAvailableSizes();

		foreach ($sizes as $key => $size) {
			if (stristr($size, 'Select size')) {
				unset($sizes[$key]);
			} else {
				$sizes[$key] = $this->format($size)->decode()->remove(' ')->sheer('-', false)->trim()->result();
			}
		}

		$product->setAvailableSizes($sizes);
	}

	protected function cleanupCrawledStockedSizes(Product $product)
	{
		$sizes = $product->getStockedSizes();

		foreach ($sizes as $key => $size) {
			if (stristr($size, 'Select size') || stristr($size, 'out of stock')) {
				unset($sizes[$key]);
			} else {
				$sizes[$key] = $this->format($size)->decode()->remove(' ')->trim()->result();
			}
		}

		$product->setStockedSizes($sizes);
	}

	protected function cleanupCrawledStyleWith(Product $product) 
	{ 

	}
}