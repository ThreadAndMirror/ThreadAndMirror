<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class FrenchConnectionFormatter extends AbstractFormatter
{
	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedName(Product $product)
	{
		$result = $this
			->format($product->getName())
			->replace($product->getBrandName().' ', '')
			->sheer(' - ', false)
			->result();

		$product->setName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedDescription(Product $product) 
	{ 
		$result = $this
			->format($product->getDescription())
			->sheer(' * ', false)->append('.')
			->result();

		$product->setDescription($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledUrl(Product $product) 
	{ 
		$result = $this
			->format($product->getUrl())
			->prepend('http://www.frenchconnection.com')
			->result();

		$product->setUrl($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledBrand(Product $product) 
	{ 
		$result = $this
			->format($product->getBrandName())
			->name()
			->result();

		$product->setBrandName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledPid(Product $product) 
	{ 
		$result = $this
			->format($product->getPid())
			->trim()
			->result();

		$product->setPid($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledImages(Product $product) 
	{ 
		$result = $this
			->format($product->getImages())
			->sheer('?', false)
			->result();

		$product->setImages($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledPortraits(Product $product) 
	{ 
		$result = $this
			->format($product->getImages())
			->sheer('?', false)->replace('731/487', '384/263')
			->result();

		$product->setImages($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledThumbnails(Product $product) 
	{ 
		$result = $this
			->format($product->getImages())
			->sheer('?', false)->replace('731/487', '117/78')
			->result();

		$product->setImages($result);
	}

	/**
	 * {@inheritdoc}
	 */
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

	/**
	 * {@inheritdoc}
	 */
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
}