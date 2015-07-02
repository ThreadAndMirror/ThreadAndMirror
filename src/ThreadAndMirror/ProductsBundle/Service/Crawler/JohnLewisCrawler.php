<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

class JohnLewisCrawler extends AbstractCrawler
{

	protected function getName(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '#prod-title span');
	}

	protected function getBrandName(DomCrawler $crawler) 
	{
		return $this->getAttributeFromElement($crawler, '.mod-brand-logo a img', 'alt');
	}

	protected function getCategoryName(DomCrawler $crawler)
	{
		return $this->getTextFromElement($crawler, '#breadcrumbs ol li:nth-child(3) a');
	}

	protected function getPid(DomCrawler $crawler) 
	{
		return $this->getTextFromElement($crawler, '#prod-product-code p');
	}

	protected function getDescription(DomCrawler $crawler) 
	{
		return $this->getTextFromList($crawler, '#tabinfo-care-info > span p');
	}

	protected function getNow(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.spcl-offr .price > span', '#prod-price .now-price');
	}

	protected function getWas(DomCrawler $crawler) 
	{
		return $this->getTextFromAlternatingElements($crawler, '.spcl-offr .now-price', null);
	}

	protected function getImages(DomCrawler $crawler) 
	{
		return $this->getSrcFromList($crawler, '#prod-media-player .js-hidden img');
	}
}