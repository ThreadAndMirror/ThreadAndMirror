<?php

namespace ThreadAndMirror\ProductsBundle\Service;

class AffiliateWindowApiService extends SoapClient
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
	protected $host = 'http://api.affiliatewindow.com/';

	/**
	 * @var The version of the API to call
	 */
	protected $version = 3;

	/**
	 * @var The the filter object for filtering result by merchant
	 */
	protected $merchant = null;

	public function __construct(Client $client, $parameters)
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
	 * Switch between the two API types
	 *
	 * @param 	string 		$function 		The name of the function to be executed
	 * @return 	self 						For chaining
	 */
	public function setMode($mode)
	{
		if ($mode === 'productServe') {
			$this->host = 'http://v'.$this->version.'.core.com.productserve.com/ProductServeService.wsdl';
		} else {
			$this->host = 'http://api.affiliatewindow.com/';
		}

		return $this;
	}

	/**
	 * The merchant to filter the searches by
	 *
	 * @param 	string 		$function 		The AW merchant ID to filter by
	 * @return 	self 						For chaining
	 */
	public function setMerchant($merchant)
	{
		$this->merchant = new \stdClass();
		$this->merchant->iId = 3;
		$this->merchant->sName = '';
		 
		$oRefineByDefinition = new \stdClass();
		$oRefineByDefinition->sId = $merchant;
		$oRefineByDefinition->sName = '';
		 
		$this->merchant->oRefineByDefinition = $oRefineByDefinition;

		return $this;
	}

	/**
	 * ProductServe API endpoint - get a list of products based on the given parameters
	 *
	 * @param  stdClass 	$merchant 	The merchant to filter the results by
	 * @param  integer  	$limit 		Maximum amount of products to receive
	 * @param  offset 		$offset 	Offset of the result set
	 * @return array 					Results JSON
	 */
	public function getProducts($merchant = null, $limit = 100, $offset = 0) 
	{
		if ($merchant instanceof \stdClass) {
			return $this->call('getProductList', array(
				'oActiveRefineByGroup' => $merchant,
				'iLimit'			   => $limit,
				'iOffset'			   => $offset
			));
		}
		
	}

	/**
	 * Get products for the current merchant
	 *
	 * @param  integer  	$limit 		Maximum amount of products to receive
	 * @param  offset 		$offset 	Offset of the result set
	 * @return array 					Results JSON
	 */
	public function getMerchantProducts($limit = 100, $offset = 0) 
	{
		return $this->getProducts($this->merchant, $offset, $limit);
	}


	/**
	 * Affiliate API endpoint
	 */
	public function getMerchant($transactions, $start, $end, $type)
	{
		return $this->call('getMerchant', array(
			'aTransactionIds' => $transactions, 
    		'dStartDate' 	  => $start, 
    		'dEndDate' 		  => $end, 
    		'sDateType' 	  => $type
		));
	}

	/**
	 * Affiliate API endpoint
	 */
	public function getMerchantList($relationship)
	{
		return $this->call('getMerchantList', array(
			'sRelationship' => $relationship
		));
	}
}