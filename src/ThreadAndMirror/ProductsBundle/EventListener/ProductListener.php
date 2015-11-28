<?php

namespace ThreadAndMirror\ProductsBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ThreadAndMirror\ProductsBundle\Event\ProductEvent;
use ThreadAndMirror\ProductsBundle\Service\ProductService;

class ProductSubscriber implements EventSubscriberInterface
{
	/** @var ProductService */
	protected $productService;

	public function __construct(ProductService $productService)
	{
		$this->productService = $productService;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getSubscribedEvents()
	{
		return [
			ProductEvent::EVENT_CREATE => 'onCreate'
		];
	}

	/**
	 * On create Product
	 *
	 * @param ProductEvent $event
	 */
	public function onCreate(ProductEvent $event)
	{
		$this->productService->cacheProduct($event->getProduct());
	}
}