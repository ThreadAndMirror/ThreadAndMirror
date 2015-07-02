<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class MatchesFashionCrawler extends AbstractCrawler
{
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

	protected function getName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#pdpMainWrapper .pdp-description');
	}

	protected function getBrandName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#pdpMainWrapper .pdp-headline a');
	}

	protected function getCategoryName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.pdp__info-wrapper .pdp-viewall li:last-child .underline a');
	}

	protected function getPid(DomCrawler $crawler)
	{
		return $this->getValueFromElement($crawler, 'input[name=baseProductCode]');
	}

	protected function getDescription(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '.pdp__info .pdp-accordion__body p', 0);
	}

	protected function getNow(DomCrawler $crawler)
	{
		return $this->getTextFromAlternatingElements($crawler, '#pdpMainWrapper .pdp-price > pdp-price__hilite', '#pdpMainWrapper .pdp-price');
	}

	protected function getWas(DomCrawler $crawler)
	{
		return $this->getTextFromAlternatingElements($crawler, '#pdpMainWrapper .pdp-price > strike', null);
	}

	protected function getImages(DomCrawler $crawler)
	{
		return $this->getSrcFromList($crawler, '.gallery-panel__main-image-carousel img');
	}

	protected function getPortraits(DomCrawler $crawler)
	{
		return $this->getSrcFromList($crawler, '.gallery-panel__main-image-carousel img');
	}

	protected function getThumbnails(DomCrawler $crawler)
	{
		return $this->getSrcFromList($crawler, '.gallery-panel__main-image-carousel img');
	}

	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#sizeSelect select option');
	}

	protected function getStockedSizes(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#sizeSelect select option');
	}

	protected function getStyleWith(DomCrawler $crawler)
	{
		return null;
	}
}