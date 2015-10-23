<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class StylebopFormatter extends AbstractFormatter
{
	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedUrl(Product $product)
	{
		$result = $this
			->format($product->getUrl())
			->sheer('murl=')
			->result();

		$product->setUrl(rawurldecode($result));
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedName(Product $product)
	{
		$result = $this->format($product->getName())
			->sheer(' - ', false)
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
			->result();

		$product->setPid($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedCategory(Product $product)
	{
		$result = $this->format($product->getCategoryName())
			->sheer('~~', false)
			->replace('Women\'s', '')
			->trim()
			->name()
			->result();

		$product->setCategoryName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedDescription(Product $product)
	{
		$result = $this->format($product->getDescription())
			->append('.')
			->result();

		$product->setDescription($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedShortDescription(Product $product)
	{
		$result = $this->format($product->getShortDescription())
			->append('.')
			->result();

		$product->setShortDescription($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedImages(Product $product)
	{

		$result = $this
			->format($product->getImages())
			->replace('/1200/', '/900/')
			->result();

		$product->setImages($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedPortraits(Product $product)
	{

		$result = $this
			->format($product->getPortraits())
			->replace('/1200/', '/230/')
			->result();

		$product->setPortraits($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedThumbnails(Product $product)
	{
		$result = $this
			->format($product->getThumbnails())
			->replace('/1200/', '/100/')
			->result();

		$product->setThumbnails($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledUrl(Product $product)
	{
		$result = $this
			->format($product->getUrl())
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
			->format($product->getThumbnail())
			->sheer('/100/')
			->sheer('.jpg', false)
			->result();

		$product->setPid($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledDescription(Product $product) 
	{
		$result = $this
			->format($product->getDescription())
			->implode()
			->append('.')
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
			->sheer('(', false)
			->currency()
			->result();

		$product->setNow($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledWas(Product $product)
	{
		$result = $this
			->format($product->getWas())
			->sheer('(', false)
			->currency()
			->result();

		$product->setWas($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledImages(Product $product) 
	{
		$result = $this
			->format($product->getThumbnails())
			->replace('/100/', '/900/')
			->result();

		$product->setImages($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledPortraits(Product $product) 
	{ 
		$result = $this
			->format($product->getThumbnails())
			->replace('/100/', '/230/')
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
			if (stristr($size, 'Waiting') || stristr($size, 'CHOOSE SIZE')) {
				unset($sizes[$key]);
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
			if (stristr($size, 'Waiting')) {
				$sizes[$key] = $this->format($size)->sheer(' »', false)->trim()->result();
			}
			if (stristr($size, 'CHOOSE SIZE')) {
				unset($sizes[$key]);
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