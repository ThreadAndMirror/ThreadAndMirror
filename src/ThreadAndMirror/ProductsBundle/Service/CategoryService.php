<?php

namespace ThreadAndMirror\ProductsBundle\Service;

use ThreadAndMirror\ProductsBundle\Entity\Category;
use ThreadAndMirror\ProductsBundle\Repository\CategoryRepository;
use ThreadAndMirror\ProductsBundle\Service\Cache\CategoryCache;

class CategoryService
{
	/** @var CategoryCache */
	protected $cache;

	/** @var CategoryRepository */
	protected $categoryRepository;

	public function __construct(CategoryCache $cache, CategoryRepository $categoryRepository)
	{
		$this->cache              = $cache;
		$this->categoryRepository = $categoryRepository;
	}

	/**
	 * Get the ID if the category already exists
	 *
	 * @param  string        $category
	 * @return string|null
	 */
	public function getExistingCategoryId($name)
	{
		// Instantiate a category from the name to generate the expected slug
		$category = new Category($name);

		// Check the cache first
		$cached = $this->cache->getData($category->guessSlug());

		if ($cached !== false) {
			return $cached['id'];
		}

		// Fall back the database directly
		$existing = $this->categoryRepository->findOneBy(['slug' => $category->guessSlug()]);

		return $existing !== null ? $existing->getId() : null;
	}
}