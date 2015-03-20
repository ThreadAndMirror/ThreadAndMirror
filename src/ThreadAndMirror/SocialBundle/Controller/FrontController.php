<?php

namespace ThreadAndMirror\SocialBundle\Controller;

use Stems\CoreBundle\Controller\BaseFrontController,
	Symfony\Component\HttpFoundation\RedirectResponse,
	Symfony\Component\HttpFoundation\Response,
	Symfony\Component\HttpFoundation\Request;


class FrontController extends BaseFrontController
{
	/**
	 * A combined feed of all latest social posts
	 *
	 * @param  Request 	$request
	 * @param  string 	$source 	The slug of the feed type to filter by, if any
	 */
	public function indexAction(Request $request, $source='all')
	{
		// get the social posts for the view
		if ($source == 'all') {
			$posts = $this->em->getRepository('ThreadAndMirrorSocialBundle:Post')->findBy(array('deleted' => false), array('created' => 'DESC'), 30);
		} else {
			$posts = $this->em->getRepository('ThreadAndMirrorSocialBundle:Post')->findBy(array('source' => $source, 'deleted' => false), array('created' => 'DESC'), 30);
		}

		return $this->render('ThreadAndMirrorSocialBundle:Front:index.html.twig', array(
			'posts' 		=> $posts,
			'page'			=> $this->page,
			'categories'	=> $this->container->getParameterBag()->get('threadandmirror.social.feed_categories'),
			'source'		=> $source,
		));
	}

	/**
	 * A combined feed of all latest social posts
	 *
	 * @param  Request 	$request
	 * @param  string 	$source 	The slug of the feed type to filter by, if any
	 */
	public function responsiveTestAction(Request $request, $source='all')
	{
		// get the social posts for the view
		if ($source == 'all') {
			$posts = $this->em->getRepository('ThreadAndMirrorSocialBundle:Post')->findBy(array('deleted' => false), array('created' => 'DESC'), 30);
		} else {
			$posts = $this->em->getRepository('ThreadAndMirrorSocialBundle:Post')->findBy(array('source' => $source, 'deleted' => false), array('created' => 'DESC'), 30);
		}

		return $this->render('ThreadAndMirrorSocialBundle:Front:index.html.twig', array(
			'posts' 		=> $posts,
			'page'			=> $this->page,
			'categories'	=> $this->container->getParameterBag()->get('threadandmirror.social.feed_categories'),
			'source'		=> $source,
		));
	}

	/**
	 * A combined feed of latest social posts from a specific category
	 *
	 * @param  Request 	$request
	 * @param  string 	$category 	The category of the feed to filter by
	 */
	public function categoryAction(Request $request, $category='store')
	{
		$categories = $this->container->getParameterBag()->get('threadandmirror.social.feed_categories');

		// Check the cateogry is in our allowed list
		if (!array_key_exists($category, $categories)) {
			return $this->redirect($this->generateUrl('thread_front_social_index'));
		}

		// Get the social posts for the view
		$posts = $this->em->getRepository('ThreadAndMirrorSocialBundle:Post')->findByOwnerCategory($category);

		// Set the dynamic page values
		$this->page->setTitle($categories[$category].' Social Circles');
		$this->page->setWindowTitle($categories[$category].' Social Circles');

		return $this->render('ThreadAndMirrorSocialBundle:Front:category.html.twig', array(
			'posts' 			=> $posts,
			'page'				=> $this->page,
			'categories'		=> $categories,
			'currentCategory'	=> $category,
		));
	}

	/**
	 * All posts for a specific feed
	 *
	 * @param  Request 	$request
	 * @param  string 	$slug 		The slug of the feed
	 */
	public function feedAction(Request $request, $slug)
	{
		$feed       = $this->em->getRepository('ThreadAndMirrorSocialBundle:Feed')->findOneBySlug($slug);
		$categories = $this->container->getParameterBag()->get('threadandmirror.social.feed_categories');

		// Redirect if the feed could not be found
		if (!$feed) {
			return $this->redirect($this->generateUrl('thread_front_social_index'));
		}

		// Get the social posts for the view
		$posts = $this->em->getRepository('ThreadAndMirrorSocialBundle:Post')->findBy(array('feed' => $feed, 'deleted' => false), array('created' => 'DESC'), 30);

		// Set the dynamic page values
		$this->page->setTitle($feed->getName().'\'s Feed');
		$this->page->setWindowTitle($feed->getName().'\'s Feed');

		return $this->render('ThreadAndMirrorSocialBundle:Front:feed.html.twig', array(
			'posts' 			=> $posts,
			'page'				=> $this->page,
			'categories'		=> $categories,
			'feed'				=> $feed,
		));
	}

	/**
	 * All posts from feeds a user has added to their favourites
	 *
	 * @param  Request 	$request
	 */
	public function mySocialCircleAction(Request $request)
	{
		// Get the user's profile and their favourite feeds
		$profile = $this->em->getRepository('ThreadAndMirrorProductsBundle:Profile')->findOneByUser($this->getUser()->getId());
		$posts   = $this->em->getRepository('ThreadAndMirrorSocialBundle:Post')->findFromFeedList($profile->getSocialFeeds());

		// Set the dynamic page values
		$this->page->setTitle($this->getUser()->getFullname().'\'s Social Circle');
		$this->page->setWindowTitle($this->getUser()->getFullname().'\'s Social Circle');

		return $this->render('ThreadAndMirrorSocialBundle:Front:myCircle.html.twig', array(
			'posts' 		=> $posts,
			'page'			=> $this->page,
		));
	}
}
