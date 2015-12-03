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
		$this->cleanupFeedShortDescription($product);
		$this->cleanupFeedNow($product);
		$this->cleanupFeedWas($product); 
		$this->cleanupFeedImages($product);
		$this->cleanupFeedPortraits($product);
		$this->cleanupFeedThumbnails($product); 
		$this->cleanupFeedAvailableSizes($product);
		$this->cleanupFeedStockedSizes($product);
		$this->cleanupFeedStyleWith($product);
		$this->cleanupFeedMetaKeywords($product);
	}

	protected function cleanupFeedName(Product $product)
	{
		$result = $this
			->format($product->getName())
			->replace($product->getBrandName().' ', '')
			->result();

		$product->setName($result);
	}

	protected function cleanupFeedUrl(Product $product) { }

	protected function cleanupFeedBrand(Product $product) { }

	protected function cleanupFeedCategory(Product $product) { }

	protected function cleanupFeedPid(Product $product) { }

	protected function cleanupFeedDescription(Product $product) 
	{ 
		$result = $this
			->format($product->getDescription())
			->decode()
			->result();

		$product->setDescription($result);
	}

	protected function cleanupFeedShortDescription(Product $product)
	{
		$result = $this
			->format($product->getShortDescription())
			->decode()
			->result();

		$product->setShortDescription($result);
	}

	protected function cleanupFeedNow(Product $product) 
	{
		$result = $this
			->format($product->getNow())
			->currency()
			->result();

		$product->setNow($result);
	}

	protected function cleanupFeedWas(Product $product) 
	{
		$result = $this
			->format($product->getWas())
			->currency()
			->result();

		$product->setWas($result);
	}

	protected function cleanupFeedImages(Product $product) { }

	protected function cleanupFeedPortraits(Product $product) { }

	protected function cleanupFeedThumbnails(Product $product) { }

	protected function cleanupFeedAvailableSizes(Product $product) { }

	protected function cleanupFeedStockedSizes(Product $product) { }

	protected function cleanupFeedStyleWith(Product $product) { }

	/**
	 * Clean up the metaKeywords generated from feed data
	 *
	 * @param Product $product
	 */
	protected function cleanupFeedMetaKeywords(Product $product)
	{
		$keywords = $product->getMetaKeywords();

		// Trim size
		$keywords = substr($keywords, 0, 254);

		$product->setMetaKeywords($keywords);
	}

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

	/**
	 * Use the requested URL by default
	 *
	 * @param Product $product
	 */
	protected function cleanupCrawledUrl(Product $product)
	{
		$result = $this
			->format($product->getUrl())
			->result();

		$product->setUrl($result);
	}

	protected function cleanupCrawledName(Product $product) { }

	protected function cleanupCrawledBrand(Product $product) { }

	protected function cleanupCrawledCategory(Product $product) { }

	protected function cleanupCrawledPid(Product $product) { }

	protected function cleanupCrawledDescription(Product $product) { }

	protected function cleanupCrawledNow(Product $product) 
	{
		$result = $this
			->format($product->getNow())
			->currency()
			->result();

		$product->setNow($result);
	}

	protected function cleanupCrawledWas(Product $product) 
	{
		$result = $this
			->format($product->getWas())
			->currency()
			->result();

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
		if (is_array($this->subject)) {
			foreach ($this->subject as $key => $value) {
				$this->subject[$key] = $left ? ltrim($value) : $value;
				$this->subject[$key] = $right ? rtrim($value) : $value;
			}
		} else {
			$this->subject = $left ? ltrim($this->subject) : $this->subject;
			$this->subject = $right ? rtrim($this->subject) : $this->subject;
		}

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
	 * @param  string 		$delimiter		    The point to perform the sheer
	 * @param  boolean 		$start 			    Whether the trim from the start of the string, false for the end
	 * @param  boolean      $discardDelimeter   Whether to discard the delimeter too
	 * @return self
	 */
	protected function sheer($delimiter, $start = true, $discardDelimeter = false)
	{	
		if (is_array($this->subject)) {
			foreach ($this->subject as $key => $value) {
				if (stristr($value, $delimiter)) {
					if ($start) {
						$explode = explode($delimiter, $value);

						// Only discard the first segement if the delimeter isn't at the start
						if (strpos($this->subject, $delimiter) !== 0) {
							unset($explode[0]);
						}

					} else {
						$explode = explode($delimiter, $value);
						$length = count($explode);
						unset($explode[$length-1]);
					}

					$this->subject[$key] = $discardDelimeter ? implode('', $explode) : implode($delimiter, $explode);
				}
			}
		} else {
			if (stristr($this->subject, $delimiter)) {
				if ($start) {
					$explode = explode($delimiter, $this->subject);

					// Only discard the first segement if the delimeter isn't at the start
					if (strpos($this->subject, $delimiter) !== 0) {
						unset($explode[0]);
					}

				} else {
					$explode = explode($delimiter, $this->subject);
					$length = count($explode);
					unset($explode[$length-1]);
				}

				$this->subject = $discardDelimeter ? implode('', $explode) : implode($delimiter, $explode);
			}
		}

		return $this;
	}

	/**
	 * Sheers the end or beginning of a string using specific rules
	 *
	 * @param  string 		$rule		The special rule to apply
	 * @param  boolean 		$start 		Whether the trim from the start of the string, false for the end
	 * @return self
	 */
	protected function sheerSpecial($rule = 'caps', $start = true)
	{
		if (is_array($this->subject)) {
			// @todo
//			foreach ($this->subject as $key => $value) {
//				if (stristr($value, $delimiter)) {
//					if ($end) {
//						$explode = explode($delimiter, $value);
//						unset($explode[0]);
//						$this->subject[$key] = implode($delimiter, $explode);
//					} else {
//						$explode = explode($delimiter, $value);
//						$length = count($explode);
//						unset($explode[$length-1]);
//						$this->subject[$key] = implode($delimiter, $explode);
//					}
//				}
//			}
		} else {
			switch ($rule)
			{
				// Sheer all capitalised words from the start of a string
				case 'caps':
					$words = explode(' ', $this->subject);
					foreach($words as $key => $word) {
						if (mb_strtoupper($word, 'utf-8') == $word) {
							unset($words[$key]);
						} else {
							break;
						}
					}
					$this->subject = implode(' ', $words);
					break;
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
		$this->subject = utf8_encode($this->subject);

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

			$list = [];

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
	 * Json decode a string
	 *
	 * @return self
	 */
	protected function json()
	{
		$this->subject = json_decode($this->subject);

		return $this;
	}

	/**
	 * Prepend text to a string or a collection fo strings
	 *
	 * @param  string 	    $string 	Text to prepend
	 * @return self
	 */
	protected function prepend($string)
	{
		if (is_array($this->subject)) {

			$list = [];

			foreach ($this->subject as $item) {
				$list[] = $item = $string.$item;
			}

			$this->subject = $list;

		} else {
			$this->subject = $string.$this->subject;
		}

		return $this;
	}

	/**
	 * Append text to a string or a collection fo strings
	 *
	 * @param  string 	    $string 	Text to append
	 * @return self
	 */
	protected function append($string)
	{
		if (is_array($this->subject)) {

			$list = [];

			foreach ($this->subject as $item) {
				$list[] = $item = $item.$string;
			}

			$this->subject = $list;

		} else {
			$this->subject = $this->subject.$string;
		}

		return $this;
	}

	/**
	 * Implode an array of strings into one string
	 *
	 * @param  string       $glue       Glue for the implode
	 * @return self
	 */
	protected function implode($glue = '. ')
	{
		$this->subject = implode($glue, $this->subject);

		return $this;
	}

	/**
	 * Explode a string
	 *
	 * @param  string       $delimiter
	 * @param  integer      $keep           Keep this index of the explode and discard the rest
	 * @return self
	 */
	protected function explode($delimiter, $keep = null)
	{
		$this->subject = explode($delimiter, $this->subject);

		if ($keep !== null) {

			// Throw an exception if the keep key doesn't exist
			if (!array_key_exists($keep, $this->subject)) {
				throw new \InvalidArgumentException('Key '.$keep.' cannot be kept from '.json_encode($this->subject));
			}

			$this->subject = $this->subject[$keep];
		}

		return $this;
	}

	/**
	 * Remove an item from an array by index or by content
	 *
	 * @param  mixed       $match
	 * @return self
	 */
	protected function discard($match)
	{
		if (is_int($match)) {
			if ($match < 0) {
				unset($this->subject[count($this->subject) - $match]);
			} else {
				unset($this->subject[$match]);
			}
		} else {
			foreach ($this->subject as $key => $item) {
				if (stristr($item, $match)) {
					unset($this->subject[$key]);
				}
			}
		}

		return $this;
	}

	/**
	 * Keep only the first element / property of the result array / stdClass object
	 *
	 * @return self
	 */
	protected function first()
	{
		if (is_array($this->subject)) {
			$this->subject = reset($this->subject);
		} else if ($this->subject instanceof \stdClass) {
			$this->subject = get_object_vars($this->subject);
			$this->subject = reset($this->subject);
		}

		return $this;
	}

	/**
	 * Keep only the last element of the result array
	 *
	 * @return self
	 */
	protected function last()
	{
		if (is_array($this->subject)) {
			$this->subject = end($this->subject);
		}

		return $this;
	}
}