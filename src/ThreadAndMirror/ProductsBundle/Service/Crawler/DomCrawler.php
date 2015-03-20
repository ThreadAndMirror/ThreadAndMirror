<?php

namespace ThreadAndMirror\ProductsBundle\Service\Crawler;

use Symfony\Component\DomCrawler\Crawler;

class DomCrawler extends Crawler
{
	/**
	 * Single encompassing method for value based position selecting
	 *
	 * @param  mixed 		$position 		The position of the element(s)
	 * @return Crawler 						The crawler object containing the desired nodes
	 */
	public function position($position) 
	{
		// First element
		if ($position === 0 || $position === 'first') {
			return $this->first();
		}

		// Last element
		if ($position === -1 || $position === 'last') {
			return $this->last();
		}

		// All child elements
		if ($position === 'children') {
			return $this->children();
		}

		// Nth element (0 based)
		if (is_numeric($position) && $position > 0) {
			return $this->eq($position);
		}

		// Nth element from the end
		if (is_numeric($position) && $position > 0) {
			$position = $this->count() + $position;
			return $this->eq($position);
		}

		return $this->first();
	}
}