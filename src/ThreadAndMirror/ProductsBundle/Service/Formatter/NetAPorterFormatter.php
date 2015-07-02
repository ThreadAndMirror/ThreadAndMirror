<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class NetAPorterFormatter extends AbstractFormatter
{
	protected function cleanupCrawledName(Product $product)
	{
		$result = $this
			->format($product->getName())
			->trim()
			->result();

		$product->setName($result);
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
			->trim()
			->result();

		$product->setCategoryName($result);
	}

	protected function cleanupCrawledPid(Product $product)
	{
		$result = $this
			->format($product->getPid())
			->result();

		$product->setPid($result);
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
			->prepend('http:')
			->replace('_xs', '_pp')
			->result();

		$product->setImages($result);
	}

	protected function cleanupCrawledPortraits(Product $product)
	{

		$result = $this
			->format($product->getPortraits())
			->prepend('http:')
			->replace('_xs', '_m')
			->result();

		$product->setPortraits($result);
	}

	protected function cleanupCrawledThumbnails(Product $product)
	{
		$result = $this
			->format($product->getThumbnails())
			->prepend('http:')
			->result();

		$product->setThumbnails($result);
	}

	protected function cleanupCrawledAvailableSizes(Product $product)
	{
		$sizes = $product->getAvailableSizes();

		foreach ($sizes as $key => $size) {
			if (stristr($size, 'Choose Your Size')) {
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
			if (stristr($size, 'Choose Your Size') || stristr($size, 'sold out')) {
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