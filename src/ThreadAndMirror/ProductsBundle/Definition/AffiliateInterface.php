<?php 

namespace ThreadAndMirror\ProductsBundle\Definition;

interface AffiliateInterface
{
	/**
	 * Convert a url string into an affiliate link
	 */
	public function getAffiliateLink($url);

	/**
	 * Update products for the active merchant
	 */
	public function updateProducts();

	/**
	 * Set the active shop
	 */
	public function setShop($merchant);
} 