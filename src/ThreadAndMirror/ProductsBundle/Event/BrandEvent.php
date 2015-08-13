<?php

namespace ThreadAndMirror\ProductsBundle\Event;
 
use Symfony\Component\EventDispatcher\Event;
use ThreadAndMirror\ProductsBundle\Entity\Brand;

class BrandEvent extends Event
{
	const EVENT_ADD = 'brand.add';

	const EVENT_UPDATE = 'brand.update';

	/** @var Brand */
	protected $brand;

    public function __construct(Brand $brand)
    {
        $this->brand = $brand;
    }

	/**
	 * Get Brand
	 *
	 * @return Brand
	 */
	public function getBrand()
	{
		return $this->brand;
	}
}