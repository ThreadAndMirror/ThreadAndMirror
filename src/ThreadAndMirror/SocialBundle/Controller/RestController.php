<?php

namespace ThreadAndMirror\SocialBundle\Controller;

use Stems\CoreBundle\Controller\BaseRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class RestController extends BaseRestController
{
	const POST_CHUNK = 30;

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
		$favourites = $profile->getSocialFeeds() ? $profile->getSocialFeeds() : [];

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
	 *
	 * @Route("/rest/social/get-more-posts/{source}/{offset}", name="thread_social_rest_more_posts")
	 */
	public function getMorePostsAction($source = 'all', $offset = 0)
	{
		$em = $this->getDoctrine()->getManager();

		// Get the posts
		if ($source == 'all') {
			$posts = $em->getRepository('ThreadAndMirrorSocialBundle:Post')->findBy(['deleted' => false], ['created' => 'DESC'], self::POST_CHUNK, $offset);
		} else {
			$posts = $em->getRepository('ThreadAndMirrorSocialBundle:Post')->findBy(['source' => $source, 'deleted' => false], ['created' => 'DESC'], self::POST_CHUNK, $offset);
		}

		// Render the html for the posts
		$html = '';

		foreach ($posts as &$post) {
			$html .= $this->renderView('ThreadAndMirrorSocialBundle:Rest:post.html.twig', [
				'post' => $post,
			]);
		}
		
		// Let the ajax response know when there's no more additional posts to load
		if (count($posts) < self::POST_CHUNK) {
			$this->setCallback('stopLoading');
		}

		return $this->addHtml($html)->success()->sendResponse();
	}

	/**
	 * Returns the html for the next n posts
	 *
	 * @param  string 	$category 	The category to filter posts by
	 * @param  integer 	$offset 	The amount of posts already loaded, and therefore the query offset
	 *
	 * @Route("/rest/social/get-more-category-posts/{category}/{offset}", name="thread_social_rest_more_category_posts")
	 */
	public function getMoreCategoryPostsAction($category = 'magazine', $offset = 0)
	{
		// Get the posts
		$em    = $this->getDoctrine()->getManager();
		$posts = $em->getRepository('ThreadAndMirrorSocialBundle:Post')->findByOwnerCategory($category, $offset, self::POST_CHUNK);

		// Render the html for the posts
		$html = '';

		foreach ($posts as &$post) {
			$html .= $this->renderView('ThreadAndMirrorSocialBundle:Rest:post.html.twig', [
				'post' => $post,
			]);
		}
		
		// Let the ajax response know when there's no more additional posts to load
		if (count($posts) < self::POST_CHUNK) {
			$this->setCallback('stopLoading');
		}

		return $this->addHtml($html)->success()->sendResponse();
	}

	/**
	 * Returns the html for the next n posts
	 *
	 * @param  string 	$slug    	The slug of the feed
	 * @param  integer 	$offset 	The amount of posts already loaded, and therefore the query offset
	 */
	public function getMoreFeedPostsAction($id, $offset = 0)
	{
		// Get the posts
		$em    = $this->getDoctrine()->getManager();
		$posts = $em->getRepository('ThreadAndMirrorSocialBundle:Post')->findBy(['feed' => $id, 'deleted' => false], ['created' => 'DESC'], $offset, self::POST_CHUNK);

		// Render the html for the posts
		$html = '';

		foreach ($posts as &$post) {
			$html .= $this->renderView('ThreadAndMirrorSocialBundle:Rest:post.html.twig', [
				'post' => $post,
			]);
		}
		
		// Let the ajax response know when there's no more additional posts to load
		if (count($posts) < self::POST_CHUNK) {
			$this->setCallback('stopLoading');
		}

		return $this->addHtml($html)->success()->sendResponse();
	}

	/**
	 * Returns the html for the next n posts that are on the users favourite list
	 *
	 * @param  integer 	$offset 	The amount of posts already loaded, and therefore the query offset
	 */
	public function getMoreFavouritePostsAction($offset)
	{
		// Get the user's profile and their favourite feeds
		$em      = $this->getDoctrine()->getManager();
		$profile = $em->getRepository('ThreadAndMirrorProductsBundle:Profile')->findOneByUser($this->getUser()->getId());
		$posts   = $em->getRepository('ThreadAndMirrorSocialBundle:Post')->findFromFeedList($profile->getSocialFeeds(), $offset, self::POST_CHUNK);

		// Render the html for the posts
		$html = '';

		foreach ($posts as &$post) {
			$html .= $this->renderView('ThreadAndMirrorSocialBundle:Rest:post.html.twig', [
				'post' => $post,
			]);
		}
		
		// Let the ajax response know when there's no more additional posts to load
		if (count($posts) < self::POST_CHUNK) {
			$this->setCallback('stopLoading');
		}

		return $this->addHtml($html)->success()->sendResponse();
	}
}
