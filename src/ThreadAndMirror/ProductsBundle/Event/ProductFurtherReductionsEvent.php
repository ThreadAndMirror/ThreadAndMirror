<?php

namespace ThreadAndMirror\ProductsBundle\Event;
 
use ThreadAndMirror\ProductsBundle\Event;
use ThreadAndMirror\ProductsBundle\Entity\Product;

class ProductFurtherReductionsEvent extends ProductEvent
{
    /**
     * @var The updated version of the product
     */
    protected $new;

    public function __construct(Product $product, Product $new)
    {
        $this->product = $product;
        $this->new     = $new;
    }

    /**
     * Get the new version of the product
     *
     * @return Product        The new product
     */
    public function getNew() 
    {
        return $this->new;
    }
}