<?php

namespace ThreadAndMirror\ProductsBundle\Service\Parser;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class JohnLewisParser
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
	}
}