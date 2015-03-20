<?php

namespace ThreadAndMirror\ProductsBundle\Event;
 
use ThreadAndMirror\ProductsBundle\Event;
use ThreadAndMirror\ProductsBundle\Entity\Product;

class ProductNewSizesInStockEvent extends ProductEvent
{
    /**
     * @var The new sizes in stock
     */
    protected $sizes;

    public function __construct(Product $product, $sizes)
    {
        $this->product = $product;
        $this->sizes   = $sizes;
    }

    /**
     * Get the sizes that are newly in stock
     *
     * @return array        A list new newly stocked sizes
     */
    public function getSizes() 
    {
        return $this->sizes;
    }
}