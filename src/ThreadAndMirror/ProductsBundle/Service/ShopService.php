<?php

namespace ThreadAndMirror\ProductsBundle\Service;

use Symfony\Component\Security\Core\Exception\InvalidArgumentException;
use ThreadAndMirror\ProductsBundle\Entity\Shop;
use ThreadAndMirror\ProductsBundle\Repository\ShopRepository;

class ShopService
{
	/** @var ShopRepository */
	protected $shopRepository;

	public function __construct(ShopRepository $shopRepository)
	{
		$this->shopRepository = $shopRepository;
	}

	/**
	 * Get all active shops
	 *
	 * @return Shop[]
	 */
	public function getActiveShops()
	{
		return $this->shopRepository->findBy(['deleted' => false], ['name' => 'ASC']);
	}

	/**
	 * Get active shops for area
	 *
	 * @return Shop[]
	 */
	public function getShopsForArea($area)
	{
		if (!in_array($area, ['fashion', 'beauty'])) {
			throw new InvalidArgumentException('Invalid area "'.$area.'"');
		}

		// @todo change this at some point
		$property = 'has'.ucfirst($area);

		return $this->shopRepository->findBy(['deleted' => false, $property => true], ['name' => 'ASC']);
	}

	/**
	 * Get a shop
	 *
	 * @param  string   $slug
	 * @return Shop
	 */
	public function getShop($slug)
	{
		return $this->shopRepository->findOneBy(['slug' => $slug]);
	}
}