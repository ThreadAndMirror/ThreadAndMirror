<?php

namespace ThreadAndMirror\SocialBundle\Controller;

use Stems\CoreBundle\Controller\BaseRestController;

class RestController extends BaseRestController
{
	/**
	 * Add a feed to the user's favourites
	 *
	 * @param  integer 	$id 	The ID of the feed owner
	 */
	public function favouriteFeedAction($id)
	{
		// Ensure the user is logged in
		if (!$this->get('security.context')->isGranted('ROLE_USER')) {
			return $this->error('You need to be logged in before you can add to your social circle.', true)->sendResponse();
		}
		
		// Load the user's profile and the feed owner
		$em      = $this->getDoctrine()->getManager();
		$profile = $em->getRepository('ThreadAndMirrorProductsBundle:Profile')->findOneByUser($this->getUser()->getId());
		$feed 	 = $em->getRepository('ThreadAndMirrorSocialBundle:Feed')->find($id);

		// Check that a feed with that id exists
		if (!$feed) {
			return $this->error('We couldn\'t find the requested social circle!', true)->sendResponse();
		}

		// Favourites can be null if none exist already
		$favourites = $profile->getSocialFeeds() ? $profile->getSocialFeeds() : array();

		// Add the feed to the user's favourites if it doesn't already exist
		if (!in_array($id, $favourites)) {

			// Save the feed as a favourite
			$favourites[] = $id;
			$profile->setSocialFeeds($favourites);
			$em->persist($profile);
			$em->flush();

			return $this->success($feed->getName().' has been added to your social circle.', true)->sendResponse();

		} else {

			// Notify that it's already a favourite
			return $this->error($feed->getName().' is already in your social circle!', true)->sendResponse();
		}	
	}

	/**
	 * Returns the html for the next n posts
	 *
	 * @param  string 	$source 	The source type to filter posts by
	 * @param  integer 	$offset 	The amount of posts already loaded, and therefore the query offset
	 * @param  integer 	$chunk 		The amount of posts to render
	 */
	public function getMorePostsAction($source='all', $offset, $chunk=50)
	{
		$em = $this->getDoctrine()->getManager();

		// Get the posts
		if ($source == 'all') {
			$posts = $em->getRepository('ThreadAndMirrorSocialBundle:Post')->findBy(array('deleted' => false), array('created' => 'DESC'), $chunk, $offset);
		} else {
			$posts = $em->getRepository('ThreadAndMirrorSocialBundle:Post')->findBy(array('source' => $source, 'deleted' => false), array('created' => 'DESC'), $chunk, $offset);
		}

		// Render the html for the posts
		$html = '';

		foreach ($posts as &$post) {
			$html .= $this->renderView('ThreadAndMirrorSocialBundle:Rest:post.html.twig', array(
				'post' 		=> $post,
			));
		}
		
		// Let the ajax response know when there's no more additional posts to load
		count($posts) < $chunk and $this->setCallback('stopLoading');

		return $this->addHtml($html)->success()->sendResponse();
	}

	/**
	 * Returns the html for the next n posts
	 *
	 * @param  string 	$category 	The category to filter posts by
	 * @param  integer 	$offset 	The amount of posts already loaded, and therefore the query offset
	 * @param  integer 	$chunk 		The amount of posts to render
	 */
	public function getMoreCategoryPostsAction($category='magazine', $offset, $chunk=50)
	{
		// Get the posts
		$em    = $this->getDoctrine()->getManager();
		$posts = $em->getRepository('ThreadAndMirrorSocialBundle:Post')->findByOwnerCategory($category, $offset, $chunk);

		// Render the html for the posts
		$html = '';

		foreach ($posts as &$post) {
			$html .= $this->renderView('ThreadAndMirrorSocialBundle:Rest:post.html.twig', array(
				'post' 		=> $post,
			));
		}
		
		// Let the ajax response know when there's no more additional posts to load
		count($posts) < $chunk and $this->setCallback('stopLoading');

		return $this->addHtml($html)->success()->sendResponse();
	}

	/**
	 * Returns the html for the next n posts
	 *
	 * @param  string 	$slug    	The slug of the feed
	 * @param  integer 	$offset 	The amount of posts already loaded, and therefore the query offset
	 * @param  integer 	$chunk 		The amount of posts to render
	 */
	public function getMoreFeedPostsAction($id, $offset, $chunk=50)
	{
		// Get the posts
		$em    = $this->getDoctrine()->getManager();
		$posts = $em->getRepository('ThreadAndMirrorSocialBundle:Post')->findBy(array('feed' => $id, 'deleted' => false), array('created' => 'DESC'), $offset, $chunk);

		// Render the html for the posts
		$html = '';

		foreach ($posts as &$post) {
			$html .= $this->renderView('ThreadAndMirrorSocialBundle:Rest:post.html.twig', array(
				'post' 		=> $post,
			));
		}
		
		// Let the ajax response know when there's no more additional posts to load
		count($posts) < $chunk and $this->setCallback('stopLoading');

		return $this->addHtml($html)->success()->sendResponse();
	}

	/**
	 * Returns the html for the next n posts that are on the users favourite list
	 *
	 * @param  integer 	$offset 	The amount of posts already loaded, and therefore the query offset
	 * @param  integer 	$chunk 		The amount of posts to render
	 */
	public function getMoreFavouritePostsAction($offset, $chunk=50)
	{
		// Get the user's profile and their favourite feeds
		$em      = $this->getDoctrine()->getManager();
		$profile = $em->getRepository('ThreadAndMirrorProductsBundle:Profile')->findOneByUser($this->getUser()->getId());
		$posts   = $em->getRepository('ThreadAndMirrorSocialBundle:Post')->findFromFeedList($profile->getSocialFeeds(), $offset, $chunk);

		// Render the html for the posts
		$html = '';

		foreach ($posts as &$post) {
			$html .= $this->renderView('ThreadAndMirrorSocialBundle:Rest:post.html.twig', array(
				'post' 		=> $post,
			));
		}
		
		// Let the ajax response know when there's no more additional posts to load
		count($posts) < $chunk and $this->setCallback('stopLoading');

		return $this->addHtml($html)->success()->sendResponse();
	}
}
