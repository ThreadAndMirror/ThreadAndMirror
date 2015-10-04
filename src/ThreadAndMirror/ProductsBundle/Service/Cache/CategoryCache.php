<?php

namespace ThreadAndMirror\ProductsBundle\Service\Cache;


use Doctrine\Common\Cache\CacheProvider;
use ThreadAndMirror\ProductsBundle\Entity\Category;

class CategoryCache
{
	const ROOT_KEY = 'category';

	const DATA_KEY = 'data';

	/** @var CacheProvider */
	protected $cache;

	/** @var array */
	protected $lifetimes;

	public function __construct(CacheProvider $cache, $lifetimes)
	{
		$this->cache     = $cache;
		$this->lifetimes = $lifetimes;
	}

	/**
	 * Cache the data for a category
	 *
	 * @param  Category      $category
	 */
	public function setData(Category $category)
	{
		$index = implode('.', array(self::ROOT_KEY, self::DATA_KEY, $category->getSlug()));

		$this->cache->save($index, true, $this->lifetimes[self::DATA_KEY]);
	}

	/**
	 * Get cached data for the category
	 *
	 * @param  string           $slug
	 * @return array|false
	 */
	public function getData($slug)
	{
		$index = implode('.', array(self::ROOT_KEY, self::DATA_KEY, $slug));

		// Return the cache value
		return $this->cache->fetch($index) !== false ? json_decode($this->cache->fetch($index), true) : false;
	}

	/**
	 * Whether cache exists for the category
	 *
	 * @param  string           $slug
	 * @return boolean
	 */
	public function exists($slug)
	{
		$index = implode('.', array(self::ROOT_KEY, self::DATA_KEY, $slug));

		// Return the cache value
		return $this->cache->fetch($index) === false ? false : true;
	}
}