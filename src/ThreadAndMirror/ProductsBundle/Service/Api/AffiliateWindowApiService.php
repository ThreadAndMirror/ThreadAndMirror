<?php

namespace ThreadAndMirror\ProductsBundle\Service\Api;

use Buzz\Browser;

class AffiliateWindowApiService extends \SoapClient
{
	/**
	 * @var The Client for making requests
	 */
	protected $client;

	/**
	 * @var The config parameters for the API
	 */
	protected $parameters;

	/**
	 * @var The user to authenticate with the API
	 */
	protected $user;

	/**
	 * @var The base url for the api
	 */
	protected $host = 'http://v3.core.com.productserve.com/ProductServeService.wsdl';

	/**
	 * @var The version of the API to call
	 */
	protected $version = 3;

	/**
	 * @var The extra product data we want to receive
	 */
	protected $productColumns = array(
		'sBrand', 
		'sModel',
		'sMerchantThumbUrl', 
		'sMerchantImageUrl',
		'sAwThumbUrl',
		'sAwImageUrl', 
		'sDescription', 
		'sMerchantProductId',
		'sCurrency',
		'fStorePrice',
		'fRrpPrice'
	);

	/**
	 * @var The extra merchant data we want to receive
	 */
	protected $merchantColumns = array(		
		'sCountryCode',
		'sStrapline',
		'sDescription',
		'sLogoUrl',
		'sDisplayUrl',
		'sClickThroughUrl',
		'oDiscountCode',
		'oCommissionRange'
	);

	/**
	 * @var The categories we want to pull products for
	 */
	protected $allowedCategories = array(
		595,149,135,163,168,159,169,161,167,170,194,141,205,198,206,203,208,199,204,201,110,111,113,114,546,547
	);

	public function __construct(Browser $client, $parameters)
	{
		$this->client     = $client;
		$this->parameters = $parameters;

		// Configure the service based on the account type
		if ($this->parameters['user_type'] === 'affiliate') {

			// Create the user object
			$this->user 		   = new \stdClass();
			$this->user->iId       = $this->parameters['publisher_username'];
    		$this->user->sPassword = $this->parameters['publisher_password'];
    		$this->user->sType     = $this->parameters['user_type'];
    		$this->user->sApiKey   = $this->parameters['api_key'];
		}

		// Create the SOAP client
		$wdsl = $this->host.'v'.$this->version.'/'.ucfirst($this->parameters['user_type']).'Service?wsdl';
		parent::__construct($wdsl, array('trace' => false, 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE));
		
		// Configure the SOAP headers
		$header  = new \SoapHeader($this->host, 'UserAuthentication', $this->user, true, $this->host);
		$headers = array($header);
		
		// getQuota only used on APIs which do not use a single API Key
		if ($this->user->sApiKey === null) {
		    $headers[] = new \SoapHeader($this->host, 'getQuota', true, true, $this->host);
		}
		
		// Set the headers
		$this->__setSoapHeaders($headers);
		
		// Set WSDL caching
		ini_set('soap.wsdl_cache_enabled', 1);
		ini_set('soap.wsdl_cache_ttl', 86400);
		
		// Set server response timeout
		ini_set('default_socket_timeout', 240);
	}

	/**
	 * Executes the speficied function from the WSDL
	 *
	 * @param 	string 		$function 		The name of the function to be executed
	 * @param 	mixed 		$parameters 	Optional paramters for the fucntion, can be array or single value
	 * @return 	mixed 						The results
	 */
	public function call($function, $parameters = '')
	{
		return $this->$function($parameters);
	}
		
	/**
	 * API endpoint - get a list of products based on the given parameters
	 *
	 * @param  integer   	$id 		The merchant ID to filter the results by
	 * @param  offset 		$offset 	Offset of the result set
	 * @param  integer  	$limit 		Maximum amount of products to receive
	 * @return array 					Results JSON
	 */
	public function getMerchantProducts($id, $offset = 0, $limit = 100)
	{
		// Filters
		$merchantFilter = $this->getFilterByMerchant($id);
		$categoryFilter = $this->getFilterByAllowedCategories();

		return $this->call('getProductList', array(
			'oActiveRefineByGroup' => array($merchantFilter, $categoryFilter),
			'iLimit'			   => $limit,
			'iOffset'			   => $offset,
			'sColumnToReturn'	   => $this->productColumns
		));
	}

	/**
	 * API endpoint - get a list of all merchants we're affiliated with
	 *
	 * @return array 					Results JSON
	 */
	public function getMerchantList() 
	{
		return $this->call('getMerchant', array(
			'sColumnToReturn'	   => $this->merchantColumns
		));
	}

	/**
	 * API endpoint - get details for a specific merchants
	 *
	 * @param  array 		$ids 		Merchant ids to find
	 * @return array 					Results JSON
	 */
	public function getMerchants($ids) 
	{
		return $this->call('getMerchant', array(
			// 'iMerchantId'	  => $ids,
			'sColumnToReturn' => $this->merchantColumns
		));
	}	


	/**
	 * Build a filter object for getting results for a specific merchant ID
	 *
	 * @param  integer 		$id 		The merchant ID
	 * @return stdClass 				The resulting filter object
	 */
	public function getFilterByMerchant($id) 
	{
		$oRefineBy = new \stdClass();
		$oRefineBy->iId = 3;
		$oRefineBy->sName = '';
		 
		$merchant = new \stdClass();
		$merchant->sId = strval($id);
		$merchant->sName = '';

		$oRefineBy->oRefineByDefinition = $merchant;

		return $oRefineBy;
	}

	/**
	 * Build a filter object for getting results for the allowed categories
	 *
	 * @return stdClass 					The resulting filter object
	 */
	public function getFilterByAllowedCategories() 
	{
		$oRefineBy = new \stdClass();
		$oRefineBy->iId = 4;
		$oRefineBy->sName = 'Category';

		$categories = array();
		 
		foreach ($this->allowedCategories as $id) {
			$category = new \stdClass();
			$category->sId = strval($id);
			$category->sName = '';
			$categories[] = $category;
		}
		
		$oRefineBy->oRefineByDefinition = $categories;

		return $oRefineBy;
	}
}
