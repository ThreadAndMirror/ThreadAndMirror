<?php

namespace ThreadAndMirror\ProductsBundle\Service;

use Doctrine\ORM\NoResultException;
use ThreadAndMirror\ProductsBundle\Entity\Brand;
use ThreadAndMirror\ProductsBundle\Repository\BrandRepository;
use ThreadAndMirror\ProductsBundle\Service\Cache\BrandCache;

class BrandService
{
	/** @var BrandCache */
	protected $cache;

	/** @var BrandRepository */
	protected $brandRepository;

	public function __construct(BrandCache $cache, BrandRepository $brandRepository)
	{
		$this->cache           = $cache;
		$this->brandRepository = $brandRepository;
	}

	/**
	 * Get the ID if the brand already exists
	 *
	 * @param  string        $brand
	 * @return string|null
	 */
	public function getExistingBrandId($name)
	{
		// Instantiate a brand from the name to generate the expected slug
		$brand = new Brand($name);

		// Check the cache first
		$cached = $this->cache->getData($brand->getSlug());

		if ($cached !== false) {
			return $cached->id;
		}

		// Fall back the database directly
		try {
			$existing = $this->brandRepository->findOneBy(['slug' => $brand->getSlug()]);

			return $existing->getId();

		} catch (NoResultException $e) {
			return null;
		}
	}
}