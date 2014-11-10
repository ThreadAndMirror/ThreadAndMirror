<?php

namespace ThreadAndMirror\SocialBundle\Service;

use Doctrine\ORM\EntityManager,
	Symfony\Bundle\TwigBundle\TwigEngine,
	Symfony\Component\Form\FormFactoryInterface,
	Symfony\Component\DependencyInjection\ContainerAware,
	ThreadAndMirror\SocialBundle\Entity\Post;

/**
 *	Functionality for parsing, updating and moderating social feeds
 */
class FeedManagerService extends ContainerAware
{
	/**
	 * a tidy pointer for the entity manager
	 */
	protected $em;

	/**
	 * Run an update on feeda of the specified type, or all if none are specified
	 *
	 * @param  string 	$type	The slug of the feed type to update  
	 * @return integer 			The amount of feeds that encountered errors
	 */
	public function updateFeeds($type=null)
	{
		// load the entity manager so it's available to the methods
		$this->em = $this->container->get('doctrine.orm.entity_manager');

		// ensure the feed type exists
		if ($type && in_array($type, $this->container->getParameterBag()->get('threadandmirror.social.feed_types'))) {

			$method = 'update'.ucfirst($type).'Feeds';
			return $this->$method();

		} else {

			// tot up the errors across the feeds as we go
			$errors = 0;

			foreach ($this->container->getParameterBag()->get('threadandmirror.social.feed_types') as $type) {
				$method = 'update'.ucfirst($type).'Feeds';
				$errors += $this->$method();
			}

			return $errors;
		}
	}

	/**
	 * Update all twitter feeds
	 *
	 * @return integer		The amount of feeds that encountered errors
	 */
	protected function updateTwitterFeeds()
	{
		// get our config controlled API params
		$token  = $this->container->getParameterBag()->get('threadandmirror.social.twitter.token');
		$errors = 0;

		// get the active feed owners
		$owners   = $this->em->getRepository('ThreadAndMirrorSocialBundle:Feed')->findActiveFeeds('twitter');
		$existing = $this->em->getRepository('ThreadAndMirrorSocialBundle:Post')->getExistingPostIds();

		// update via the API for each owner
		foreach ($owners as $owner) {

			// add the bearer token to the request header
			$options = array(10023 => array('Authorization: Bearer '.$token));

			// build the url for the api call
			$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name='.$owner->getTwitterHandle();

			// use curl the get their feed data
			$json = $this->apiRequest($url, $options);
	   
			// sometimes twitter won't send the response, so we need to catch it
			try
			{
				// loop through the tweetss in the data and turn into posts
				foreach ($json as $tweet) {

					// only add the tweets we haven't already downloaded
					if (!in_array($tweet->id, $existing)) {
						$post = new Post('twitter', $tweet);
						$post->setFeed($owner);
						$this->em->persist($post);
					}
				}

				$this->em->flush();
			}
			catch (\Exception $e) 
			{
				$errors++;
			}
		}

		return $errors;
	}

	/**
	 * Update all tumblr feeds
	 *
	 * @return integer		The amount of feeds that encountered errors
	 */
	protected function updateTumblrFeeds()
	{
		// Get our config controlled API params
		$token  = $this->container->getParameterBag()->get('threadandmirror.social.tumblr.token');
		$errors = 0;

		// Get the active feed owners
		$owners   = $this->em->getRepository('ThreadAndMirrorSocialBundle:Feed')->findActiveFeeds('tumblr');
		$existing = $this->em->getRepository('ThreadAndMirrorSocialBundle:Post')->getExistingPostIds();

		// Update via the API for each owner
		foreach ($owners as $owner) {

			// Build the url for the api call
			$url = 'https://api.tumblr.com/v2/blog/'.$owner->getTumblrHandle().'.tumblr.com/posts?limit=20&api_key='.$token;

			// Use curl the get their feed data
			$json = $this->apiRequest($url);
	   
			// Sometimes tumblr won't send the response, so we need to catch it
			try
			{
				// Loop through the posts in the data and turn into posts
				foreach ($json->response->posts as $post) {

					// Only add the posts we haven't already downloaded
					if (!in_array($post->id, $existing)) {
						$post = new Post('tumblr', $post);
						$post->setFeed($owner);	
						$this->em->persist($post);
					}
				}

				$this->em->flush();
			}
			catch (\Exception $e) 
			{
				$errors++;
			}
		}

		return $errors;
	}

	/**
	 * Update all instagram feeds
	 *
	 * @return integer		The amount of feeds that encountered errors
	 */
	protected function updateInstagramFeeds()
	{
		// get our config controlled API params
		$client = $this->container->getParameterBag()->get('threadandmirror.social.instagram.client');
		$token  = $this->container->getParameterBag()->get('threadandmirror.social.instagram.token');
		$count  = $this->container->getParameterBag()->get('threadandmirror.social.instagram.count');
		$errors = 0;

		// get the active feed owners and existing post sids
		$owners   = $this->em->getRepository('ThreadAndMirrorSocialBundle:Feed')->findActiveFeeds('instagram');
		$existing = $this->em->getRepository('ThreadAndMirrorSocialBundle:Post')->getExistingPostIds();

		// update via the API for each owner
		foreach ($owners as $owner) {

			// generate the instgram ID if we don't have it already
			if (!$owner->getInstagramId()) {
				$this->generateInstagramId($owner, $token);
				$this->em->persist($owner);
				$this->em->flush();
			}

			// build the url for the api call
			$url = 'https://api.instagram.com/v1/users/'.$owner->getInstagramId().'/media/recent/?access_token='.$token.'&count='.$count;

			// use curl the get their feed data
			$json = $this->apiRequest($url);
	   
			// sometimes instagram won't send the response, so we need to catch it
			try
			{
				// loop through the instagrams in the data and turn into posts
				foreach ($json->data as $instagram) {

					// add the images we haven't already downloaded
					if (!in_array($instagram->id, $existing)) {
						$post = new Post('instagram', $instagram);
						$post->setFeed($owner);
						$this->em->persist($post);
					}
				}

				$this->em->flush();
			}
			catch (\Exception $e) 
			{
				$errors++;
			}
		}

		return $errors;
	}

	/**
	 * Use curl the call the Instagram API to get their user ID from their username
	 *
	 *  @param  Feed 	$owner 		The feed owner to generate the ID for
	 *	@param  string 		$token 		The app-only bearer token for the API request
	 */
	public function generateInstagramId($owner, $token)
	{
		// we need both the handle and api token to find the ID
		if ($owner->getInstagramHandle() && $token) {
		
			$url = 'https://api.instagram.com/v1/users/search?q='.$owner->getInstagramHandle().'&access_token='.$token;

			// call the API
			$json = $this->apiRequest($url);

			// add the id to the entity, leaving persitance upto whatever called the method
			$owner->setInstagramId($json->data[0]->id);

		} else {
			throw new \Exception('Cannot generate the Instagram ID without both the Instgram handle and API access token.');
		}
	}

	/**
	 * Fires a common API request based in a precompiled url and returns the decoded JSON
	 *
	 * @param  string 	$url 		The url to make the API request too
	 * @param  array 	$options 	Any additional curl options to set
	 * @return array 				Decoded JSON of the API response
	 */
	public function apiRequest($url, $options=array())
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);

		// add any custom options
		foreach ($options as $option => $value) {
			curl_setopt($ch, $option, $value);
		}

		$json = curl_exec($ch);
		curl_close($ch); 

		// return the decoded result
		return json_decode($json);
	}
}
