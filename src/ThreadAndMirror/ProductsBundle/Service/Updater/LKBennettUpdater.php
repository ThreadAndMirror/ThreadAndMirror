<?php

namespace ThreadAndMirror\ProductsBundle\Service\Updater;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class LKBennettUpdater extends AbstractUpdater
{
	/**
	 * Update the name of the product to tidy up from feed
	 *
	 * @param Product 	$new 		The new product 
	 * @param Product 	$product 	The original product	
	 */
	protected function updateName(Product $new, Product $product)
	{
		if ($product->getName() !== $new->getName()) {
			$product->setName($new->getName());
		}
	}
}