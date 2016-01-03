<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

use Buzz\Browser;
use Symfony\Bridge\Monolog\Logger;
use ThreadAndMirror\ProductsBundle\Entity\Product;
use ThreadAndMirror\ProductsBundle\Exception\CrawlException;
use ThreadAndMirror\ProductsBundle\Definition\CrawlerInterface;
use ThreadAndMirror\ProductsBundle\Exception\ProductParseException;

abstract class AbstractCrawler implements CrawlerInterface
{
	/** @var Browser */
	protected $client;

	/**
	 * @var Headers to add to each request
	 */
	protected $headers = array(
		'User-Agent' 		=> 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36',
		'Accept' 			=> 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Language' 	=> 'en-gb,en;q=0.5',
		'Accept-Charset' 	=> 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
		'Keep-Alive' 		=> '115',
		'Connection' 		=> 'keep-alive'
	);

	/**
	 * @var Logger
	 */
	protected $logger;

	public function crawl($url) 
	{
		$product = new Product();
		$product->setUrl($url);

		$crawler = new DomCrawler();
		$crawler->addHTMLContent($this->client->get($url, $this->headers)->getContent(), 'UTF-8');

		// Check if a page is no longer available before crawling
		if ($this->getExpired($crawler)) {
			$product->setExpired(new \DateTime());
			return $product;
		}

		// Try to crawl each piece of data individually and throw our custom exception for a more descriptive error
		try {
			if ($this->getUrl($crawler) == null) {
				$product->setUrl($url);
			} else {
				$product->setUrl($this->getUrl($crawler));
			}
		} catch (\Exception $e) {
			$message = 'Crawl Error getting Url - '.$url.' - '.$e->getMessage();
			$this->logger->error($message);
			throw new ProductParseException($message);
		}

		try {
			$product->setName($this->getName($crawler));
		} catch (\Exception $e) {
			$message = 'Crawl Error getting Name - '.$url.' - '.$e->getMessage();
			$this->logger->error($message);
			throw new ProductParseException($message);
		} 
		try {
			$product->setBrandName($this->getBrandName($crawler));
		} catch (\Exception $e) {
			$message = 'Crawl Error getting Brand - '.$url.' - '.$e->getMessage();
			$this->logger->error($message);
			throw new ProductParseException($message);
		} 
		try {
			$product->setCategoryName($this->getCategoryName($crawler));
		} catch (\Exception $e) {
			$message = 'Crawl Error getting Category - '.$url.' - '.$e->getMessage();
			$this->logger->error($message);
			throw new ProductParseException($message);
		}
		try {
			$product->setPid($this->getPid($crawler));
		} catch (\Exception $e) {
			$message = 'Crawl Error getting Pid - '.$url.' - '.$e->getMessage();
			$this->logger->error($message);
			throw new ProductParseException($message);
		}
		try {
			$product->setDescription($this->getDescription($crawler));
		} catch (\Exception $e) {
			$message = 'Crawl Error getting Description - '.$url.' - '.$e->getMessage();
			$this->logger->error($message);
			throw new ProductParseException($message);
		}
		try {
			$product->setNow($this->getNow($crawler));
		} catch (\Exception $e) {
			$message = 'Crawl Error getting Now - '.$url.' - '.$e->getMessage();
			$this->logger->error($message);
			throw new ProductParseException($message);
		}
		try {
			$product->setImages($this->getImages($crawler));
		} catch (\Exception $e) {
			$message = 'Crawl Error getting Images - '.$url.' - '.$e->getMessage();
			$this->logger->error($message);
			throw new ProductParseException($message);
		}
		try {
			$product->setPortraits($this->getPortraits($crawler));
		} catch (\Exception $e) {
			$message = 'Crawl Error getting Images - '.$url.' - '.$e->getMessage();
			$this->logger->error($message);
			throw new ProductParseException($message);
		}
		try {
			$product->setThumbnails($this->getThumbnails($crawler));
		} catch (\Exception $e) {
			$message = 'Crawl Error getting Thumbnails - '.$url.' - '.$e->getMessage();
			$this->logger->error($message);
			throw new ProductParseException($message);
		}

		// Non-critical, so don't throw exception
		try {
			$product->setWas($this->getWas($crawler));
		} catch (\Exception $e) {
			$this->logger->warning('Crawl Error getting Was - '.$url.' - '.$e->getMessage());
		}
		try {
			$product->setAvailableSizes($this->getAvailableSizes($crawler));
		} catch (\Exception $e) {
			$this->logger->warning('Crawl Error getting AvailableSizes - '.$url.' - '.$e->getMessage());
		}
		try {
			$product->setStockedSizes($this->getStockedSizes($crawler));
		} catch (\Exception $e) {
			$this->logger->warning('Crawl Error getting StockedSizes - '.$url.' - '.$e->getMessage());
		}
		try {
			$product->setStyleWith($this->getStyleWith($crawler));
		} catch (\Exception $e) {
			$this->logger->warning('Crawl Error getting StyleWith - '.$url.' - '.$e->getMessage());
		}

		return $product;
	}

	protected function getExpired(DomCrawler $crawler) 
	{
		return false;
	}

	protected function getUrl(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getName(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getBrandName(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getCategoryName(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getPid(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getDescription(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getNow(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getWas(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getImages(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getPortraits(DomCrawler $crawler) 
	{
		return $this->getImages($crawler);
	}

	protected function getThumbnails(DomCrawler $crawler) 
	{
		return $this->getImages($crawler);
	}

	protected function getAvailableSizes(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getStockedSizes(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getStyleWith(DomCrawler $crawler) 
	{
		return null;
	}

	protected function getTextFromElement(DomCrawler $crawler, $selector, $position = 0)
	{
		return $crawler->filter($selector)->position($position)->text();
	}

	protected function getAttributeFromElement(DomCrawler $crawler, $selector, $attr, $position = 0)
	{
		return $crawler->filter($selector)->position($position)->attr($attr);
	}

	protected function getSrcFromElement(DomCrawler $crawler, $selector, $position = 0)
	{
		return $this->getAttributeFromElement($crawler, $selector, 'src', $position);
	}

	protected function getHrefFromElement(DomCrawler $crawler, $selector, $position = 0)
	{
		return $this->getAttributeFromElement($crawler, $selector, 'href', $position);
	}

	protected function getValueFromElement(DomCrawler $crawler, $selector, $position = 0)
	{
		return $this->getAttributeFromElement($crawler, $selector, 'value', $position);
	}

	protected function getDataFromElement(DomCrawler $crawler, $selector, $data, $position = 0)
	{
		return $this->getAttributeFromElement($crawler, $selector, 'data-'.$data, $position);
	}

	/**
	 * Get text from an element, but try a different element if it doesn't exist. 
	 * Good for sale/non-sale price-containing elements.
	 *
	 * @param  Crawler 	$crawler 			The crawler object for the product page
	 * @param  string 	$first				The selector of the first element to find
	 * @param  string 	$second				The selector of the second element, set to null to catch exception
	 * @param  string 	$eqFirst			The position of the first element to find
	 * @param  string 	$eqSecond			The position of the second element
	 * @return string 						The text contained within the found element
	 */
	protected function getTextFromAlternatingElements(DomCrawler $crawler, $first, $second, $eqFirst = 0, $eqSecond = 0)
	{
		try {
 			return $this->getTextFromElement($crawler, $first, $eqFirst); 
		} catch (\Exception $e) {
			if ($second === null) {
				return null;
			} else {
				return $this->getTextFromElement($crawler, $second, $eqSecond); 
			}
		}
	}

	/**
	 * Get text for each element in a list. 
	 * Good for getting sizes.
	 *
	 * @param  Crawler 	$crawler 			The crawler object for the product page
	 * @param  string 	$selector			The selector for the list items
	 * @return string 						The text contained within the found element
	 */
	protected function getTextFromList(DomCrawler $crawler, $selector)
	{
		// Get the text from each list item
		$list = $crawler->filter($selector)->each(function ($node, $i) {
			return $node->text();
		});

		return $list;
	}

	/**
	 * Get data attributes for each element in a list. 
	 * Good for getting images and links.
	 *
	 * @param  Crawler 	$crawler 			The crawler object for the product page
	 * @param  string 	$selector			The selector for the list items
	 * @return string 						The attribute value on the found element
	 */
	protected function getAttributesFromList(DomCrawler $crawler, $selector, $attr)
	{
		// Get the attribute from each list item
		$list = $crawler->filter($selector)->each(function ($node, $i) {
   			return $node;
		}); 

		foreach ($list as $key => $node) {
			$list[$key] = $node->attr($attr);
		}

		return $list;
	}

	protected function getValuesFromList(DomCrawler $crawler, $selector)
	{
		return $this->getAttributesFromList($crawler, $selector, 'value');
	}

	protected function getSrcFromList(DomCrawler $crawler, $selector)
	{
		return $this->getAttributesFromList($crawler, $selector, 'src');
	}

	protected function getHrefFromList(DomCrawler $crawler, $selector)
	{
		return $this->getAttributesFromList($crawler, $selector, 'href');
	}

	protected function getDataFromList(DomCrawler $crawler, $selector, $data)
	{
		return $this->getAttributesFromList($crawler, $selector, 'data-'.$data);
	}

	public function setLogger(Logger $logger) 
	{
		$this->logger = $logger;
	}

	public function setClient(Browser $client) 
	{
		$this->client = $client;
	}
}