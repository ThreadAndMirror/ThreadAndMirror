<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class LookFantasticFormatter extends AbstractFormatter
{
	protected function cleanupFeedImages(Product $product) 
	{ 
		$result = $this->format($product->getImage())->replace('130/130', '600/600')->result();
		$product->setImages(array($result));
	}

	protected function cleanupFeedPortraits(Product $product) 
	{ 
		$result = $this->format($product->getImage())->replace('600/600', '300/300')->result();
		$product->setPortraits(array($result));
	}

	protected function cleanupFeedThumbnails(Product $product) 
	{ 
		$result = $this->format($product->getImage())->replace('600/600', '130/130')->result();
		$product->setThumbnails(array($result));
	}

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
			->result();

		$product->setBrandName($result);
	}

	protected function cleanupCrawledPid(Product $product)
	{
		$result = $this
			->format($product->getPid())
			->result();

		$product->setPid($result);
	}

	protected function cleanupCrawledWas(Product $product)
	{
		if ($product->getWas() != null) {

			$result = $this
				->format($product->getWas())
				->currency()
				->result();
			$product->setWas($product->getNow() + (($result / 100) * $product->getNow()));
		}
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
			->replace('300/300', '600/600')
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
			->replace('300/300', '180/180')
			->result();

		$product->setThumbnails($result);
	}
}