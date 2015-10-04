<?php

namespace ThreadAndMirror\ProductsBundle\Service\Cache;


use Doctrine\Common\Cache\CacheProvider;
use ThreadAndMirror\ProductsBundle\Entity\Brand;
use ThreadAndMirror\ProductsBundle\Entity\Product;

class ProductCache
{
	const ROOT_KEY = 'product';

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
	 * Cache the data for a product
	 *
	 * @param  string       $key
	 * @param  array        $data
	 */
	public function setData($key, $data)
	{
		$index = implode('.', array(self::ROOT_KEY, self::DATA_KEY, $key));

		$this->cache->save($index, json_encode($data), $this->lifetimes[self::DATA_KEY]);
	}

	/**
	 * Get cached data for the product
	 *
	 * @param  string            $key
	 * @return array|false
	 */
	public function getData($key)
	{
		$index = implode('.', array(self::ROOT_KEY, self::DATA_KEY, $key));

		// Return the cache value
		return $this->cache->fetch($index) !== false ? json_decode($this->cache->fetch($index), true) : false;
	}

	/**
	 * Whether cache exists for the product
	 *
	 * @param  string           $key
	 * @return boolean
	 */
	public function exists($key)
	{
		$index = implode('.', array(self::ROOT_KEY, self::DATA_KEY, $key));

		// Return the cache value
		return $this->cache->fetch($index) === false ? false : true;
	}
}