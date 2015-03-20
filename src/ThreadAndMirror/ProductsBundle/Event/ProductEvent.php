<?php

namespace ThreadAndMirror\ProductsBundle\Event;
 
use Symfony\Component\EventDispatcher\Event;
use ThreadAndMirror\ProductsBundle\Entity\Product;

class ProductEvent extends Event
{
    public function __construct(Product $product)
    {
        $this->product = $product;
    }
}