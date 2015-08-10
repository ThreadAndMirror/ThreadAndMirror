<?php

namespace ThreadAndMirror\ProductsBundle\Event;
 
use Symfony\Component\EventDispatcher\Event;
use ThreadAndMirror\ProductsBundle\Entity\Product;

class ProductEvent extends Event
{
	const EVENT_ADD = 'product.add';

	const EVENT_UPDATE = 'product.update';

	/** @var Product */
	protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

	/**
	 * Get Product
	 *
	 * @return Product
	 */
	public function getProduct()
	{
		return $this->product;
	}
}