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

// 'http://datafeed.api.productserve.com/datafeed/download/apikey/9ef4bc95b428aecc0d24c5d810524a8f/cid/141,205,198,206,203,208,199,204,201,110,111,113,114,546,547/fid/2017,3208,5678,6009/columns/aw_product_id,merchant_product_id,merchant_category,aw_deep_link,merchant_image_url,search_price,description,product_name,merchant_deep_link,aw_image_url,merchant_name,merchant_id,category_name,category_id,delivery_cost,currency,store_price,rrp_price,merchant_thumb_url,in_stock,stock_quantity,specifications,brand_name,brand_id,display_price,data_feed_id,last_updated,colour,large_image,size/format/csv/delimiter/,/compression/gzip/adultcontent/1/''