<?php

namespace ThreadAndMirror\ProductsBundle\Service\Parser;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class AsosParser
{
	/**
	 * Post-processing for feed product creation
	 *
	 * @param  Product 		$product 	The product to cleanup
	 */
	public function cleanupFeedProduct(Product $product) 
	{
		// For some reason some shops stick the colour in the name...
		if (stristr($product->getName(), ' - ')) {
			$product->setName(substr($product->getName(), 0, strpos($product->getName(), ' - ')));
		}
	}
}