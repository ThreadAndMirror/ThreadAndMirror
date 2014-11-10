<?php

namespace ThreadAndMirror\ProductsBundle\Affiliate;

use ThreadAndMirror\ProductsBundle\Definition\AffiliateInterface;

class AffiliateWindowAffiliate extends ContainerAware implements AffiliateInterface 
{
	public function getAffiliateLink($url)
	{
		return 'http://www.awin1.com/awclick.php?mid=1142&id=45628&clickref=123456&p='.rawurldecode($url);
	}
}