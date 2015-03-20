<?php 

namespace ThreadAndMirror\ProductsBundle\Definition;

interface CrawlerInterface
{
	/**
	 * Crawls a url
	 */
	public function crawl($url);
} 