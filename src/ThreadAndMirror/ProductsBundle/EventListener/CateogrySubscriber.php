<?php

namespace ThreadAndMirror\ProductsBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ThreadAndMirror\ProductsBundle\Event\CategoryEvent;
use ThreadAndMirror\ProductsBundle\Service\CategoryService;

class CategorySubscriber implements EventSubscriberInterface
{
	/** @var CategoryService */
	protected $cateogryService;

	public function __construct(CategoryService $cateogryService)
	{
		$this->cateogryService = $cateogryService;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getSubscribedEvents()
	{
		return [
			CategoryEvent::EVENT_CREATE => 'onCreate'
		];
	}

	/**
	 * On create cateogry
	 *
	 * @param CategoryEvent $event
	 */
	public function onCreate(CategoryEvent $event)
	{
		$this->cateogryService->cacheCategory($event->getCategory());
	}
}