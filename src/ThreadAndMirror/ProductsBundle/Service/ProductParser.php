<?php

namespace ThreadAndMirror\ProductsBundle\Service;

use Symfony\Component\DomCrawler\Crawler,
	Symfony\Bridge\Monolog\Logger,
	Doctrine\ORM\EntityManager;

/**
 * For curling and crawling product pages, as well as providing utility for the individual parsers.
 */
class ProductParser
{	
	/**
 	 * Holds the active curl handle instance
 	 */
	protected $ch;
	
	/**
	 * For storing the resulting html of a product crawl
	 */
	protected $html = '';
	
	/**
	 *  For storing the primary url of a product crawl
	 */
	protected $url = '';
	
	/**
	 * The entity manager
	 */
	protected $em;
	
	/**
	 * the crawler instance
	 */
	protected $crawler;

	/**
	 * The logger service
	 */
	protected $logger;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->logger = new Logger('threadandmirror');

		// start a curl instance
		$this->ch = curl_init ();

		// timeout the connection if it takes too long
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 100);

		// this tells curl to return the data
		curl_setopt ($this->ch, CURLOPT_RETURNTRANSFER, 1);

		// follow redirects if any
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);

		// tell curl if the data is binary data or not
		curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, 0);

		// ignore SSL
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->ch, CURLOPT_VERBOSE, true);

		// set the http header to prevent sites attempting to block non-browser access, including all the rubbish to get through matchesfashion.com
		$header = array(
		  'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
		  'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		  'Accept-Language: en-gb,en;q=0.5',
		  'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
		  'Keep-Alive: 115',
		  'Connection: keep-alive',
		  'Cookie:
		  __cfduid=d61d16c7a06ae5e2d590494cea7dce12d1398280685548; 
		  prev_user=yes; 
		  SERVERID=www1; 
		  3E7B8005-0E96-41B8-8C62-A5401BF43BCE=LBH1eoBKJfj4uVCU7+d6IfZw+dRvSBF9TAF8SLa7oAtGN5td9q/UsL4+3XjizGnC8Gut9zFH+x4Q3gZkzzJDoAtAzcauONs3CEQ4LWC2go8VGZuxnTu2bYm8d6LXOs9dSDc+lDXjPK/UhMvoAQgVHnI2qKh5Ic1brm+3AcMmI8KrHdtztYYLbLk8klFvD0MEainAzOZJFoGz7NwtnHWpI5OjycJ35Kl0sLohOOV1pt16onTXUdKXnu3GdQ/cusQDxrFBc6kdsEPKcjkLWWMLVs6Q1Rj7dbNnIa+zihb8EbTE6kAT/AOcgQ==; 
		  mfn_rv=1; 
		  mfn_sc=GBR; 
		  mfn_bc=GBP; 
		  mfn_ic=GBP; 
		  AKSB=s=1402004502216;
		  mfn_au=false; 
		  mfn_it=0; 
		  0197638E-88FF-44E8-BD5F-4F05EDF2D6A6=5wrcnBgpKRXipOS6MhviEEqDuh0hbx6FWUEWgp4+Ptv92YWxjc1jewhLTl+ehcdSYalHkyfT1hN+V1ox+KeCY8XJ1XMj1G5qN1MNiizbvcjP6hEFB2ukcU17baLsCTK++N3vT2tJcodWfWPIfRy65A==; 
		   _#sess=1401830628%7C20140603220519%7C3;
		   _#env=20140604213519;
		   _#srchist=30065%3A1400229472%3A20140615083752%7C30065%3A1400229522%3A20140615083842%7C30065%3A1400239735%3A20140615112855%7C30065%3A1400239761%3A20140615112921%7C30065%3A1400254433%3A20140615153353%7C30065%3A1400255098%3A20140615154458%7C30065%3A1400255121%3A20140615154521%7C30065%3A1400365402%3A20140616222322%7C30065%3A1400365501%3A20140616222501%7C30065%3A1400399733%3A20140617075533%7C30065%3A1400405597%3A20140617093317%7C30065%3A1400424493%3A20140617144813%7C30065%3A1400424661%3A20140617145101%7C30065%3A1400440705%3A20140617191825%7C30065%3A1400497205%3A20140618110005%7C30065%3A1400497237%3A20140618110037%7C30065%3A1400507349%3A20140618134909%7C30065%3A1400509035%3A20140618141715%7C30065%3A1400708831%3A20140620214711%7C15844%3A1401041394%3A20140624180954%7C15844%3A1401041663%3A20140624181423%7C15844%3A1401042023%3A20140624182023%7C30065%3A1401138538%3A20140625210858%7C15844%3A1401264567%3A20140627080927%7C15844%3A1401264676%3A20140627081116%7C15844%3A1401265084%3A20140627081804%7C15844%3A1401281974%3A20140627125934%7C30065%3A1401286073%3A20140627140753%7C15844%3A1401287766%3A20140627143606%7C15844%3A1401303072%3A20140627185112%7C38929%3A1401303830%3A20140627190350%7C15844%3A1401356680%3A20140628094440%7C15844%3A1401398713%3A20140628212513%7C15844%3A1401398736%3A20140628212536%7C15844%3A1401443337%3A20140629094857%7C15844%3A1401443418%3A20140629095018%7C30065%3A1401451339%3A20140629120219%7C15844%3A1401483395%3A20140629205635%7C30065%3A1401483591%3A20140629205951%7C30065%3A1401483611%3A20140629210011%7C15844%3A1401638579%3A20140701160259%7C15844%3A1401642850%3A20140701171410%7C15844%3A1401705001%3A20140702103001%7C15844%3A1401705005%3A20140702103005%7C15844%3A1401811034%3A20140703155714%7C15844%3A1401811124%3A20140703155844%7C15844%3A1401811297%3A20140703160137%7C15846%3A1401828670%3A20140604205110%7C30065%3A1401829950%3A20140703211230%7C30065%3A1401830628%3A20140703212348;
		   _#vdf=30065%7C1401830628%7C20140703212348;
		   _#lps=0%7C20140603213519;
		   stl=',
		);

		curl_setopt($this->ch, CURLOPT_FAILONERROR, false); 
		curl_setopt($this->ch, CURLOPT_COOKIEFILE,'cookies.txt');
		curl_setopt($this->ch, CURLOPT_COOKIEJAR,'cookies.txt');
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
			

		return $this;
	}

	/**
	 * Terminates the curl instance if we no longer need it
	 *
	 * @return boolean 		Whether the curl instance was closed successfully
	 */
	public function close()
	{
		try 
		{
			curl_close ($this->ch);
			return true;
		} 
		catch (Exception $e) 
		{
			return false;
		}
	}

	/**
	 * Takes a url, gets the html and then builds a new crawler instance from it and stores it in the service
	 *
	 * @param  string  	$url 	The url to be crawled
	 * @return Crawler 			The crawler instance generated from the html 		
	 */
	public function crawl($url)
	{
		// set the URL to scrape
		$this->url = $url;

		if (isset($this->url)) {
			
			// set the url to download
			curl_setopt ($this->ch, CURLOPT_URL, $this->url);

			// grabs the webpage from the internets
			$this->html = curl_exec($this->ch);
		}

		// create a new crawler with the page html, ensuring we use the right character encoding
		$this->crawler = new Crawler();
		$this->crawler->addHTMLContent($this->html, 'UTF-8');

		return $this->crawler;
	}

	/**
	 * Perform a crawl without overiding the current product url
	 *
	 * @param  string  	$url 	The url to be crawled
	 * @return Crawler 			The crawler instance generated from the html 		
	 */
	public function crawlCustomUrl($url)
	{
		// set the url to download
		curl_setopt ($this->ch, CURLOPT_URL, $url);

		// grabs the webpage from the internets
		$html = curl_exec($this->ch);

		// create a new crawler with the xml response
 		$crawler = new Crawler($html);

		return $crawler;
	}

	/**
	 * Cleanup special characters and anomolies from a parsed string
	 *
	 * @param  string  	$string 	The string to be cleaned
	 * @return string 				The cleaned string 		
	 */
	public function cleanupString($string)
	{
		// html entities
		$string = html_entity_decode($string);

		// control characters
		$string = preg_replace('/(?=[^\n\r\t])\p{Cc}/u', '', $string);

		// multiple whitespaces
		$string = preg_replace('!\s+!', ' ', $string);

		return $string;
	}
	
	/**
	 * Get the entity manager
	 *
	 * @return EntityManager 	The entity manager 		
	 */
	public function getManager()
	{
		return $this->em;
	}

	/**
	 * Get the crawler instance
	 *
	 * @return Crawler 		  	The stored crawler instance 	
	 */
	public function getCrawler()
	{
		return $this->crawler;
	}

	/**
	 * Get the logger instance
	 *
	 * @return Logger 		  	The stored logger instance 	
	 */
	public function getLogger()
	{
		return $this->logger;
	}
}
