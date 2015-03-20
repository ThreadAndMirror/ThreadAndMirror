<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;

class LibertyLondonFormatter extends AbstractFormatter
{
	protected function cleanupFeedName(Product $product) 
	{ 
		$result = $this->format($product->getName())
			->sheer(' size ')
			->result();

		$product->setName($result);
	}

	public function cleanupFeedDescription(Product $product) 
	{
		// Get feature lists out of the description, if they exist
		if (stristr($product->getDescription(), '•')) {

			// Bullet list
			$list = substr($product->getDescription(), strpos($product->getDescription(), '•'));
			$list = explode('•', $list);
			unset($list[0]);
			$list = '<ul><li>'.implode('</li><li>', $list).'</li></ul>';

			$result = $this->format($product->getDescription())
				->sheer('•')
				->replace('Features', '</p><h6>Features</h6><p>')
				->append($list)
				->result();

			$product->setDescription($result);
		} else {
			$result = $this->format($product->getDescription())
				->replace('FEATURES', '</p><h6>FEATURES</h6><p>')
				->result();

			$product->setDescription($result);
		}

	}

	protected function cleanupFeedImages(Product $product) 
	{ 

	}

	protected function cleanupFeedPortraits(Product $product) 
	{ 
		$result = $this->format($product->getImage())
			->replace('large1', 'medium')
			->result();

		$product->setPortraits(array($result));
	}

	protected function cleanupFeedThumbnails(Product $product) 
	{ 
		$result = $this->format($product->getImage())
			->replace('large1', 'medium')
			->result();

		$product->setThumbnails(array($result));
	}
}