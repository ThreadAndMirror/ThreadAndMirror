<?php

namespace ThreadAndMirror\ProductsBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ThreadAndMirror\ProductsBundle\Event\BrandEvent;
use ThreadAndMirror\ProductsBundle\Service\BrandService;

class BrandSubscriber implements EventSubscriberInterface
{
	/** @var BrandService */
	protected $brandService;

	public function __construct(BrandService $brandService)
	{
		$this->brandService = $brandService;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getSubscribedEvents()
	{
		return [
			BrandEvent::EVENT_CREATE => 'onCreate'
		];
	}

	/**
	 * On create brand
	 *
	 * @param BrandEvent $event
	 */
	public function onCreate(BrandEvent $event)
	{
		$this->brandService->cacheBrand($event->getBrand());
	}
}