<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class NetAPorterFormatter extends AbstractFormatter
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
			->sheer(',', false)
			->sheer(',', false)
			->sheer(',', false)
			->replace($product->getBrandName().' ', '')
			->trim()
			->result();

		$product->setName($result);
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
			->result();

		$product->setCategoryName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedDescription(Product $product)
	{
		$result = $this->format($product->getDescription())
			->sheer($product->getName().',')
			->replace($product->getBrandName().' ', '')
			->sheer('Size:', false)
			->trim()
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
			->sheer($product->getName().',')
			->replace($product->getBrandName().' ', '')
			->sheer('Size:', false)
			->trim()
			->append('.')
			->result();

		$product->setShortDescription($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedPid(Product $product)
	{
		$metaData = json_decode($product->getMetaData());

		$result = $this
			->format($metaData->sku)
			->sheer('-', false)
			->result();

		$product->setPid($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupFeedImages(Product $product)
	{
		$result = $this
			->format($product->getImages())
			->replace('_xl', '_pp')
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
			->replace('_xl', '_m')
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
			->replace('_xl', '_xs')
			->result();

		$product->setThumbnails($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledName(Product $product)
	{
		$result = $this
			->format($product->getName())
			->trim()
			->result();

		$product->setName($result);
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
			->trim()
			->result();

		$product->setCategoryName($result);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function cleanupCrawledPid(Product $product)
	{
		$result = $this
			->format($product->getPid())
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
			->prepend('http:')
			->replace('_xs', '_pp')
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
			->prepend('http:')
			->replace('_xs', '_m')
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
			->prepend('http:')
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
			if (stristr($size, 'Choose Your Size')) {
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
			if (stristr($size, 'Choose Your Size') || stristr($size, 'sold out')) {
				unset($sizes[$key]);
			} else {
				$sizes[$key] = $this->format($size)->decode()->remove(' ')->sheer('-', false)->trim()->result();
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