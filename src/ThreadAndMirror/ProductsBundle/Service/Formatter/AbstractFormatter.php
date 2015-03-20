<?php

namespace ThreadAndMirror\ProductsBundle\Service\Formatter;

use ThreadAndMirror\ProductsBundle\Entity\Product;
use ThreadAndMirror\ProductsBundle\Definition\FormatterInterface;

abstract class AbstractFormatter implements FormatterInterface
{
	/**
	 * The current subject of a formatting chain (eg. a string)
	 */
	protected $subject = null;

	/**
	 * Post-processing for feed product creation, defaults to no action
	 *
	 * @param  Product 		$product 	The product to cleanup
	 */
	public function cleanupFeedProduct(Product $product) 
	{
		$this->cleanupFeedUrl($product);
		$this->cleanupFeedName($product);
		$this->cleanupFeedBrand($product);
		$this->cleanupFeedCategory($product);
		$this->cleanupFeedPid($product);
		$this->cleanupFeedDescription($product);
		$this->cleanupFeedNow($product);
		$this->cleanupFeedWas($product); 
		$this->cleanupFeedImages($product);
		$this->cleanupFeedPortraits($product);
		$this->cleanupFeedThumbnails($product); 
		$this->cleanupFeedAvailableSizes($product);
		$this->cleanupFeedStockedSizes($product);
		$this->cleanupFeedStyleWith($product);
	}
	protected function cleanupFeedUrl(Product $product) { }

	protected function cleanupFeedName(Product $product) { }

	protected function cleanupFeedBrand(Product $product) { }

	protected function cleanupFeedCategory(Product $product) { }

	protected function cleanupFeedPid(Product $product) { }

	protected function cleanupFeedDescription(Product $product) 
	{ 
		$result = $this->format($product->getDescription())->decode()->result();
		$product->setDescription($result);
	}

	protected function cleanupFeedNow(Product $product) 
	{
		$result = $this->format($product->getNow())->currency()->result();
		$product->setNow($result);
	}

	protected function cleanupFeedWas(Product $product) 
	{
		$result = $this->format($product->getWas())->currency()->result();
		$product->setWas($result);
	}

	protected function cleanupFeedImages(Product $product) { }

	protected function cleanupFeedPortraits(Product $product) { }

	protected function cleanupFeedThumbnails(Product $product) { }

	protected function cleanupFeedAvailableSizes(Product $product) { }

	protected function cleanupFeedStockedSizes(Product $product) { }

	protected function cleanupFeedStyleWith(Product $product) { }

	/**
	 * Post-processing for crawled products
	 *
	 * @param  Product 		$product 	The product to cleanup
	 */
	public function cleanupCrawledProduct(Product $product) 
	{
		$this->cleanupCrawledUrl($product);
		$this->cleanupCrawledName($product);
		$this->cleanupCrawledBrand($product);
		$this->cleanupCrawledCategory($product);
		$this->cleanupCrawledPid($product);
		$this->cleanupCrawledDescription($product);
		$this->cleanupCrawledNow($product);
		$this->cleanupCrawledWas($product); 
		$this->cleanupCrawledImages($product);
		$this->cleanupCrawledPortraits($product);
		$this->cleanupCrawledThumbnails($product); 
		$this->cleanupCrawledAvailableSizes($product);
		$this->cleanupCrawledStockedSizes($product);
		$this->cleanupCrawledStyleWith($product);
	}

	protected function cleanupCrawledUrl(Product $product) { }

	protected function cleanupCrawledName(Product $product) { }

	protected function cleanupCrawledBrand(Product $product) { }

	protected function cleanupCrawledCategory(Product $product) { }

	protected function cleanupCrawledPid(Product $product) { }

	protected function cleanupCrawledDescription(Product $product) { }

	protected function cleanupCrawledNow(Product $product) 
	{
		$result = $this->format($product->getNow())->currency()->result();
		$product->setNow($result);
	}

	protected function cleanupCrawledWas(Product $product) 
	{
		$result = $this->format($product->getWas())->currency()->result();
		$product->setWas($result);
	}

	protected function cleanupCrawledImages(Product $product) { }

	protected function cleanupCrawledPortraits(Product $product) { }

	protected function cleanupCrawledThumbnails(Product $product) { }

	protected function cleanupCrawledAvailableSizes(Product $product) { }

	protected function cleanupCrawledStockedSizes(Product $product) { }

	protected function cleanupCrawledStyleWith(Product $product) { }

	/**
	 * Begins a formatting operation chain
	 *
	 * @param  mixed 		$subject 	The data subject to begin formatting on
	 * @return self
	 */
	protected function format($subject)
	{
		$this->subject = $subject;

		return $this;
	}

	/**
	 * Gets the final result of a formatting operation chain
	 *
	 * @return mixed 		$subject 	The formatted data
	 */
	protected function result()
	{
		return $this->subject;
	}

	/**
	 * Trims whitespace from a string
	 *
	 * @param  boolean 		$left		Whether to perform an ltrim
	 * @param  boolean 		$right		Whether to perform an rtrim
	 * @return self
	 */
	protected function trim($left = true, $right = true)
	{
		$this->subject = $left ? ltrim($this->subject) : $this->subject;
		$this->subject = $right ? rtrim($this->subject) : $this->subject;

		return $this;
	}

	/**
	 * Converts a string into a currency float
	 *
	 * @return self
	 */
	protected function currency()
	{
		if ($this->subject !== null) {
			$this->subject = floatval(preg_replace('/[^0-9.]/', '', $this->subject));
		}

		return $this;
	}

	/**
	 * Sheers the end or beginning of a string after (and including) a specified character(s)
	 *
	 * @param  string 		$delimiter		The point to perform the sheer
	 * @param  boolean 		$end 			Whether the trim from the end, false for the beginning
	 * @return self
	 */
	protected function sheer($delimiter, $end = true)
	{	
		if (is_array($this->subject)) {
			foreach ($this->subject as $key => $value) {
				if (stristr($value, $delimiter)) {
					if ($end) {
						$explode = explode($delimiter, $value);
						unset($explode[0]);
						$this->subject[$key] = implode($delimiter, $explode);
					} else {
						$explode = explode($delimiter, $value);
						$length = count($explode);
						unset($explode[$length-1]);
						$this->subject[$key] = implode($delimiter, $explode);
					}
				}
			}
		} else {
			if (stristr($this->subject, $delimiter)) {
				if ($end) {
					$explode = explode($delimiter, $this->subject);
					unset($explode[0]);
					$this->subject = implode($delimiter, $explode);
				} else {
					$explode = explode($delimiter, $this->subject);
					$length = count($explode);
					unset($explode[$length-1]);
					$this->subject = implode($delimiter, $explode);
				}
			}
		}

		return $this;
	}

	/**
	 * Extracts part of a string between two delimeters
	 *
	 * @param  string 		$start			The delimeter at the start of the segement
	 * @param  string 		$end 			The delimeter at the end of the segement
	 * @return self
	 */
	protected function extract($start, $end)
	{
		if (stristr($this->subject, $start)) {
			$this->subject = substr($this->subject, strpos($this->subject, $start));
		}
		if (stristr($this->subject, $end)) {
			$this->subject = substr($this->subject, 0, strpos($this->subject, $end));
		}

		return $this;
	}

	/**
	 * Remove a substring or collection of substrings from a string
	 *
	 * @param  mixed 		$remove			The substring(s) to remove
	 * @return self
	 */
	protected function remove($strings)
	{
		if (is_array($strings)) {
			foreach ($strings as $string) {
				$this->subject = str_replace($string, '', $this->subject);
			}
		} else {
			$this->subject = str_replace($strings, '', $this->subject);
		}
		
		return $this;
	}

	/**
	 * Formats a string's case as if it was a name
	 *
	 * @return self
	 */
	protected function name() 
	{
		$this->subject = strtolower($this->subject);
		$this->subject = ucwords($this->subject);

		return $this;
	}

	/**
	 * Replace one part of a string with another
	 *
	 * @return self
	 */
	protected function replace($search, $replace)
	{
		if (is_array($this->subject)) {

			$list = array();

			foreach ($this->subject as $item) {
				$list[] = str_replace($search, $replace, $item);
			}

			$this->subject = $list;

		} else {
			$this->subject = str_replace($search, $replace, $this->subject);
		}

		return $this;
	}

	/**
	 * Convert html characters
	 *
	 * @return self
	 */
	protected function decode()
	{
		$this->subject = html_entity_decode($this->subject);

		return $this;
	}

	/**
	 * Prepend text to a string
	 *
	 * @param string 	$string 	Text to prepend
	 * @param self
	 */
	protected function prepend($string)
	{
		$this->subject = $string.$this->subject;

		return $this;
	}

	/**
	 * Append text to a string
	 *
	 * @param string 	$string 	Text to append
	 * @param self
	 */
	protected function append($string)
	{
		$this->subject = $this->subject.$string;

		return $this;
	}
}