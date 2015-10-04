<?php

namespace ThreadAndMirror\ProductsBundle\EventListener;

use ThreadAndMirror\ProductsBundle\Event\BrandEvent;
use ThreadAndMirror\ProductsBundle\Service\BrandService;
use ThreadAndMirror\ProductsBundle\Service\Cache\BrandCache;

class BrandListener
{
	/** @var BrandService */
	protected $brandService;

	public function __construct(BrandService $brandService)
	{
		$this->brandService = $brandService;
	}

	/**
	 * On brand.create
	 *
	 * @param BrandEvent $event
	 */
	public function onCreate(BrandEvent $event)
	{
		$this->brandService->cacheBrand($event->getBrand());
	}
}