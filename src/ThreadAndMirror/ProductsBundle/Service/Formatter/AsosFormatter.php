<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class AsosFormatter extends AbstractFormatter
{
	protected function cleanupFeedName(Product $product) 
	{ 
		$result = $this
			->format($product->getName())
			->remove('ASOS CURVE')
			->remove('ASOS MATERNITY')
			->remove('ASOS')
			->sheer(' - ', false)
			->result();

		$product->setName($result);
	}

	protected function cleanupFeedDescription(Product $product) 
	{ 
		$result = $this
			->format($product->getName())
			->sheer('ABOUT')
			->append('.')
			->result();

		$product->setDescription($result);
	}

	protected function cleanupFeedImages(Product $product) 
	{ 
		$product->setImages(array($product->getImages()[0]));
	}

	protected function cleanupFeedThumbnails(Product $product) 
	{ 
		$result = $this
			->format($product->getImages()[0])
			->replace('image1xl.jpg', 'image1s.jpg')
			->result();

		$product->setThumbnails(array($result));
	}

	protected function cleanupFeedPortraits(Product $product) 
	{ 
		$product->setPortraits(array($product->getImages()[0]));
	}
}