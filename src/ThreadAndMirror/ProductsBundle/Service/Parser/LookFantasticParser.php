<?php

namespace ThreadAndMirror\ProductsBundle\Service\Parser;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class LookFantasticParser
{
	/**
	 * Post-processing for feed product creation
	 *
	 * @param  Product 		$product 	The product to cleanup
	 */
	public function cleanupFeedProduct(Product $product) 
	{
		$product->setImage(str_replace('130/130', '600/600', $product->getImage()));
		$product->setThumbnail(str_replace('70/70', '300/300', $product->getThumbnail()));
	}
}