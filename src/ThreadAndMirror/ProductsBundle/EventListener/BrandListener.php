<?php

namespace ThreadAndMirror\ProductsBundle\EventListener;

use ThreadAndMirror\ProductsBundle\Event\BrandEvent;
use ThreadAndMirror\ProductsBundle\Service\BrandService;
use ThreadAndMirror\ProductsBundle\Service\Cache\BrandCache;

class BrandListener
{
	/** @var BrandService */
	protected $brandService;

	/** @var BrandCache */
	protected $brandCache;

	public function __construct(BrandService $brandService, BrandCache $brandCache)
	{
		$this->brandService = $brandService;
		$this->brandCache   = $brandCache;
	}

	/**
	 * On brand.create
	 *
	 * @param BrandEvent $event
	 */
	public function onCreate(BrandEvent $event)
	{
		$this->brandCache->setData($event->getBrand());
	}
}