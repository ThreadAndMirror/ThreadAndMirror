<?php

namespace ThreadAndMirror\ProductsBundle\Service\Parser;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class BaseParser
{
	/**
	 * Post-processing for feed product creation, defaults to no action
	 *
	 * @param  Product 		$product 	The product to cleanup
	 */
	public function cleanupFeedProduct(Product $product) 
	{
		return;
	}
}