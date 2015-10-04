<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class LKBennettFormatter extends AbstractFormatter
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
	protected function cleanupCrawledUrl(Product $product) 
	{ 
		$result = $this
			->format($product->getUrl())
			->prepend('http://www.lkbennett.com')
			->result();

		$product->setUrl($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledName(Product $product) 
	{
		$result = $this
			->format($product->getName())
			->name()
			->result();

		$product->setName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledBrand(Product $product)
	{

	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledPid(Product $product) 
	{ 
		$result = $this
			->format($product->getUrl())
			->sheer('/p/', '?')
			->decode()
			->result();

		$product->setPid($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledDescription(Product $product) 
	{ 
		$strings = array(
			'Description',
			'If the size you require is sold out, please contact our Customer Care Team on +44 (0)20 7637 6731.',
		);

		$result = $this
			->format($product->getDescription())
			->remove($strings)
			->trim()
			->result();

		$product->setDescription($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledNow(Product $product) 
	{
		$result = $this
			->format($product->getNow())
			->sheer('-', false)
			->currency()
			->result();

		$product->setNow($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledImages(Product $product) 
	{ 
		$result = $this
			->format($product->getImages())
			->replace('$productverticalcarousel$', '$productmainimage566$')
			->result();

		$product->setImages([$result]);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledPortraits(Product $product) 
	{
		$result = $this
			->format($product->getPortraits())
			->result();

		$product->setPortraits([$result]);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledThumbnails(Product $product) 
	{
		$result = $this
			->format($product->getThumbnails())
			->replace('$productverticalcarousel$', '$lister$')
			->result();

		$product->setThumbnails([$result]);
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
				$sizes[$key] = $this->format($size)
					->decode()
					->remove(' ')
					->sheer('-', false)
					->trim()
					->result();
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
			if (stristr($size, 'Select Size') || stristr($size, 'Out of stock')) {
				unset($sizes[$key]);
			} else {
				$sizes[$key] = $this
					->format($size)
					->decode()
					->remove(' ')
					->sheer('-', false)
					->trim()
					->result();
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