<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class NetAPorterCrawler extends AbstractCrawler
{
	/**
	 * {@inheritdoc}
	 */
	protected function getExpired(DomCrawler $crawler) 
	{
		// Page not found
		// try {
		// 	if ($this->getTextFromElement($crawler, '#breadcrumb > ul > li > a', 'last') === 'Page Not Found') {
		// 		return true;
		// 	} else {
		// 		return false;
		// 	}
		// } catch (\Exception $e) { }
		
		// // Redirected back to category page
		// try {
		// 	if ($this->getTextFromElement($crawler, '.filteroptions') !== null) {
		// 		return true;
		// 	}
		// } catch (\Exception $e) {
			return false;
		// }
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#main-product h2.product-name');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getBrandName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#main-product h1');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getCategoryName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.view-more ul li a', 1);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getPid(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#main-product .product-code span');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.editors-notes .wrapper p', 0);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getNow(DomCrawler $crawler)
	{
		return $this->getAttributeFromElement($crawler, '#main-product .full-price', 'content');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getWas(DomCrawler $crawler)
	{
		return null; // $this->getAttributeFromElement($crawler, '#main-product nap-product-price', 'price');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getThumbnails(DomCrawler $crawler)
	{
		return $this->getSrcFromList($crawler, '.thumbnails .thumbnail-image');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#sku option');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getStockedSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#sku option');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getStyleWith(DomCrawler $crawler)
	{
		return null;
	}
}