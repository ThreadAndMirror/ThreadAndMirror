<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class JohnLewisFormatter extends AbstractFormatter
{
	/**
	 * Post-processing for feed product creation
	 *
	 * @param  Product 		$product 	The product to cleanup
	 */
	public function cleanupFeedProduct(Product $product) 
	{
		$product->setImage(str_replace('fash_product', 'prod_main', $product->getImage()));
		$product->setThumbnail(str_replace('prod_thmb', 'prod_grid3', $product->getThumbnail()));

		$this->cleanupFeedProductDefaults($product);
	}

	protected function cleanupCrawledName(Product $product)
	{
		$result = $this
			->format($product->getName())
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

	protected function cleanupCrawledCategory(Product $product)
	{
		$result = $this
			->format($product->getCategoryName())
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
			->implode(' ')
			->result();

		$product->setDescription($result);
	}

	protected function cleanupCrawledImages(Product $product)
	{
		$result = $this
			->format($product->getImages())
			->result();

		$product->setImages($result);
	}

	protected function cleanupCrawledPortraits(Product $product)
	{
		$result = $this
			->format($product->getPortraits())
			->replace('$prod_main$', '$prod_grid3$')
			->result();

		$product->setPortraits($result);
	}

	protected function cleanupCrawledThumbnails(Product $product)
	{
		$result = $this
			->format($product->getThumbnails())
			->replace('$prod_main$', '$prod_thmb2$')
			->result();

		$product->setThumbnails($result);
	}
}