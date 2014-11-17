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
	protected $productColumns = array (
		'sBrand', 
		'sModel',
		'sMerchantThumbUrl', 
		'sMerchantImageUrl', 
		'sDescription', 
		'sMerchantProductId',
		'sCurrency',
		'fStorePrice',
		'fRrpPrice'
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
	 * ProductServe API endpoint - get a list of products based on the given parameters
	 *
	 * @param  integer   	$merchant 		The merchant to filter the results by
	 * @param  offset 		$offset 		Offset of the result set
	 * @param  integer  	$limit 			Maximum amount of products to receive
	 * @return array 						Results JSON
	 */
	public function getMerchantProducts($merchant, $offset = 0, $limit = 100) 
	{
		$oRefineBy = $this->getFilterByMerchant($merchant);

		return $this->call('getProductList', array(
			'oActiveRefineByGroup' => $oRefineBy,
			'iLimit'			   => $limit,
			'iOffset'			   => $offset,
			'sColumnToReturn'	   => $this->productColumns
		));
	}

	/**
	 * Build a filter object for getting results for a specific merchant ID
	 *
	 * @param  integer 		$merchant 		The merchant ID
	 * @return stdClass 					The resulting filter object
	 */
	public function getFilterByMerchant($merchant) 
	{
		$oRefineBy = new \stdClass();
		$oRefineBy->iId = 3;
		$oRefineBy->sName = '';
		 
		$oRefineByDefinition = new \stdClass();
		$oRefineByDefinition->sId = strval($merchant);
		$oRefineByDefinition->sName = '';

		$oRefineBy->oRefineByDefinition = $oRefineByDefinition;

		return $oRefineBy;
	}
}