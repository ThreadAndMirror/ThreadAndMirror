<?php

namespace ThreadAndMirror\ProductsBundle\Event;
 
use Symfony\Component\EventDispatcher\Event;
use ThreadAndMirror\ProductsBundle\Entity\Category;

class CategoryEvent extends Event
{
	const EVENT_CREATE = 'category.create';

	const EVENT_UPDATE = 'category.update';

	/** @var Category */
	protected $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

	/**
	 * Get Category
	 *
	 * @return Category
	 */
	public function getCategory()
	{
		return $this->category;
	}
}