<?php

namespace ThreadAndMirror\ProductsBundle\Util;

use Gedmo\Sluggable\Util\Urlizer;

class StringSanitizer
{
	/**
	 * Slugify a string
	 *
	 * @param  string       $string
	 * @param  string       $seperator
	 * @return string
	 */
	public static function slugify($string, $seperator = '-')
	{
		$slug = trim($string);
		$slug = Urlizer::transliterate($slug, $seperator);
		$slug = Urlizer::urlize($slug, $seperator);
		$slug = mb_strtolower($slug);

		return $slug;
	}
}