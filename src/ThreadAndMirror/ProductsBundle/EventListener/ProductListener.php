<?php

namespace ThreadAndMirror\ProductsBundle\EventListener;

use ThreadAndMirror\ProductsBundle\Event\ProductEvent;
use ThreadAndMirror\ProductsBundle\Service\ProductService;

class ProductListener
{
	/** @var ProductService */
	protected $productService;

	public function __construct(ProductService $productService)
	{
		$this->productService = $productService;
	}

	/**
	 * On product.create
	 *
	 * @param ProductEvent $event
	 */
	public function onCreate(ProductEvent $event)
	{
		$this->productService->cacheProduct($event->getProduct());
	}
}