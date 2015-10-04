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
	 * @param  string        $name
	 * @return string|null
	 */
	public function getExistingBrandId($name)
	{
		// Instantiate a brand from the name to generate the expected slug
		$brand = new Brand($name);

		// Check the cache first
		$cached = $this->cache->getData($brand->guessSlug());

		if ($cached !== false) {
			return $cached['id'];
		}

		// Fall back the database directly
		$existing = $this->brandRepository->findOneBy(['slug' => $brand->guessSlug()]);

		return $existing !== null ? $existing->getId() : null;
	}

	/**
	 * Caches the brand
	 *
	 * @param  Brand    $brand
	 */
	public function cacheBrand(Brand $brand)
	{
		$this->cache->setData($brand->getSlug(), $brand->getJson());
	}
}