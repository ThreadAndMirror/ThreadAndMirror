<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class LKBennettFormatter extends AbstractFormatter
{
	/**
	 * Post-processing for feed product creation
	 *
	 * @param  Product 		$product 	The product to cleanup
	 */
	public function cleanupFeedProduct(Product $product) 
	{

	}

	protected function cleanupCrawledUrl(Product $product) 
	{ 
		$result = $this->format($product->getUrl())->prepend('http://www.lkbennett.com')->result();
		$product->setUrl($result);
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
		$result = $this->format($product->getUrl())->sheer('/p/', '?')->result();
		$product->setPid($result);
	}

	protected function cleanupCrawledDescription(Product $product) 
	{ 
		$strings = array(
			'Description',
			'If the size you require is sold out, please contact our Customer Care Team on +44 (0)20 7637 6731.',
		);

		$result = $this->format($product->getDescription())->remove($strings)->trim()->result();
		$product->setDescription($result);
	}

	protected function cleanupCrawledNow(Product $product) 
	{
		$result = $this->format($product->getNow())->sheer('-', false)->currency()->result();
		$product->setNow($result);
	}

	protected function cleanupCrawledImages(Product $product) 
	{ 
		$result = $this->format($product->getImages())->replace('$productverticalcarousel$', '$productmainimage566$')->result();
		$product->setImages($result);
	}

	protected function cleanupCrawledPortraits(Product $product) 
	{ 
		$result = $this->format($product->getThumbnails())->replace('$productverticalcarousel$', '$lister$')->result();
		$product->setThumbnails($result);
	}

	protected function cleanupCrawledThumbnails(Product $product) 
	{ 

	}

	protected function cleanupCrawledAvailableSizes(Product $product) 
	{
		$sizes = $product->getAvailableSizes();

		foreach ($sizes as $key => $size) {
			if (stristr($size, 'Select Size')) {
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
			if (stristr($size, 'Select Size') || stristr($size, 'Out of stock')) {
				unset($sizes[$key]);
			} else {
				$sizes[$key] = $this->format($size)->decode()->remove(' ')->sheer('-', false)->trim()->result();
			}
		}

		$product->setStockedSizes($sizes);
	}

	protected function cleanupCrawledStyleWith(Product $product) 
	{ 

	}
}