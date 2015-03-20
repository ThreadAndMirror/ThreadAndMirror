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
}