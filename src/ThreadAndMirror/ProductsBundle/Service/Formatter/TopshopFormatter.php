<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class TopshopFormatter extends AbstractFormatter
{
	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedName(Product $product) 
	{ 
		$result = $this->format($product->getName())
			->sheer(' - ', false)
			->replace('**', '')
			->replace('Womens ', '')
			->replace('MATERNITY', 'Maternity')
			->replace($product->getBrandName().' ', '')
			->trim()
			->result();

		$product->setName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedPid(Product $product)
	{
		$metaData = json_decode($product->getMetaData());

		$result = $this
			->format($metaData->sku)
			->sheer('TS', true, true)
			->result();

		$product->setPid($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedImages(Product $product) 
	{ 
		$result = $this->format($product->getImages())
			->result();

		$product->setImages($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedPortraits(Product $product) 
	{ 
		$result = $this->format($product->getImages())
			->replace('_large.jpg', '_normal.jpg')
			->result();

		$product->setPortraits($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedThumbnails(Product $product) 
	{ 
		$result = $this->format($product->getThumbnails())
			->replace('_large.jpg', '_thumb.jpg')
			->result();

		$product->setThumbnails($result);
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
	protected function cleanupCrawledCategory(Product $product)
	{
		$result = $this
			->format($product->getCategoryName())
			->result();

		$product->setCategoryName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledDescription(Product $product) 
	{ 
		$result = $this
			->format($product->getDescription())
			->trim()
			->result();

		$product->setDescription($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledImages(Product $product) 
	{ 
		$result = $this
			->format($product->getImages())
			->replace('_normal', '_large')
			->result();

		$product->setImages($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledPortraits(Product $product) 
	{ 
		$result = $this
			->format($product->getPortraits())
			->result();

		$product->setPortraits($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledThumbnails(Product $product) 
	{
		$result = $this
			->format($product->getThumbnails())
			->replace('_normal', '_thumb')
			->result();

		$product->setThumbnails($result);
	}

	/**
	 * {@inheritdoc}
	 */
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

	/**
	 * {@inheritdoc}
	 */
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

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledStyleWith(Product $product) 
	{ 

	}
}