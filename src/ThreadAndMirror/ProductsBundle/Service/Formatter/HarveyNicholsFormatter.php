<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class HarveyNicholsFormatter extends AbstractFormatter
{
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
			->remove(['(', ')'])
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
			->format($product->getPortraits())
			->replace('390x546', '268x375')
			->result();

		$product->setPortraits($result);
	}

	protected function cleanupCrawledThumbnails(Product $product) 
	{
		$result = $this
			->format($product->getPortraits())
			->replace('390x546', '268x375')
			->result();

		$product->setThumbnails($result);
	}

	protected function cleanupCrawledAvailableSizes(Product $product) 
	{
		$result = $this
			->format($product->getAvailableSizes())
			->explode('"display_size":"')
			->sheer('"}', false)
			->sheer('"}]', false)
			->discard(0)
			->discard(-1)
			->result();

		$product->setAvailableSizes($result);
	}

	protected function cleanupCrawledStockedSizes(Product $product) 
	{
		$result = $this
			->format($product->getStockedSizes())
			->sheer('"lowStock":{')
			->sheer('}}', false)
			->explode(',')
			->sheer('":')
			->sheer('"', false)
			->result();

		// Use the resulting array to discard available sizes that are out of stock
		$sizes = $product->getAvailableSizes();

  		foreach ($sizes as $key => $size) {
			if ($result[$key - 1] == 1) {
				unset($sizes[$key]);
			}
		}

		$product->setStockedSizes($sizes);
	}

	protected function cleanupCrawledStyleWith(Product $product) 
	{

	}
}