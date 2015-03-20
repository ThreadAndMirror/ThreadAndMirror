<?php

namespace ThreadAndMirror\MoodBoardBundle\Controller;

use Stems\CoreBundle\Controller\BaseFrontController,
	Symfony\Component\HttpFoundation\RedirectResponse,
	Symfony\Component\HttpFoundation\Response,
	Symfony\Component\HttpFoundation\Request;


class FrontController extends BaseFrontController
{
	/**
	 * A combined feed of all latest MoodBoard posts
	 *
	 * @param  Request 	$request
	 * @param  string 	$source 	The slug of the feed type to filter by, if any
	 */
	public function indexAction(Request $request, $source='all')
	{
		// get the MoodBoard posts for the view
		if ($source == 'all') {
			$posts = $this->em->getRepository('ThreadAndMirrorMoodBoardBundle:Post')->findBy(array('deleted' => false), array('created' => 'DESC'), 30);
		} else {
			$posts = $this->em->getRepository('ThreadAndMirrorMoodBoardBundle:Post')->findBy(array('source' => $source, 'deleted' => false), array('created' => 'DESC'), 30);
		}

		return $this->render('ThreadAndMirrorMoodBoardBundle:Front:index.html.twig', array(
			'posts' 		=> $posts,
			'page'			=> $this->page,
			'categories'	=> $this->container->getParameterBag()->get('threadandmirror.MoodBoard.feed_categories'),
			'source'		=> $source,
		));
	}

	/**
	 * A combined feed of all latest MoodBoard posts
	 *
	 * @param  Request 	$request
	 * @param  string 	$source 	The slug of the feed type to filter by, if any
	 */
	public function responsiveTestAction(Request $request, $source='all')
	{
		// get the MoodBoard posts for the view
		if ($source == 'all') {
			$posts = $this->em->getRepository('ThreadAndMirrorMoodBoardBundle:Post')->findBy(array('deleted' => false), array('created' => 'DESC'), 30);
		} else {
			$posts = $this->em->getRepository('ThreadAndMirrorMoodBoardBundle:Post')->findBy(array('source' => $source, 'deleted' => false), array('created' => 'DESC'), 30);
		}

		return $this->render('ThreadAndMirrorMoodBoardBundle:Front:index.html.twig', array(
			'posts' 		=> $posts,
			'page'			=> $this->page,
			'categories'	=> $this->container->getParameterBag()->get('threadandmirror.MoodBoard.feed_categories'),
			'source'		=> $source,
		));
	}

	/**
	 * A combined feed of latest MoodBoard posts from a specific category
	 *
	 * @param  Request 	$request
	 * @param  string 	$category 	The category of the feed to filter by
	 */
	public function categoryAction(Request $request, $category='store')
	{
		$categories = $this->container->getParameterBag()->get('threadandmirror.MoodBoard.feed_categories');

		// Check the cateogry is in our allowed list
		if (!array_key_exists($category, $categories)) {
			return $this->redirect($this->generateUrl('thread_front_MoodBoard_index'));
		}

		// Get the MoodBoard posts for the view
		$posts = $this->em->getRepository('ThreadAndMirrorMoodBoardBundle:Post')->findByOwnerCategory($category);

		// Set the dynamic page values
		$this->page->setTitle($categories[$category].' MoodBoard Circles');
		$this->page->setWindowTitle($categories[$category].' MoodBoard Circles');

		return $this->render('ThreadAndMirrorMoodBoardBundle:Front:category.html.twig', array(
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
		$feed       = $this->em->getRepository('ThreadAndMirrorMoodBoardBundle:Feed')->findOneBySlug($slug);
		$categories = $this->container->getParameterBag()->get('threadandmirror.MoodBoard.feed_categories');

		// Redirect if the feed could not be found
		if (!$feed) {
			return $this->redirect($this->generateUrl('thread_front_MoodBoard_index'));
		}

		// Get the MoodBoard posts for the view
		$posts = $this->em->getRepository('ThreadAndMirrorMoodBoardBundle:Post')->findBy(array('feed' => $feed, 'deleted' => false), array('created' => 'DESC'), 30);

		// Set the dynamic page values
		$this->page->setTitle($feed->getName().'\'s Feed');
		$this->page->setWindowTitle($feed->getName().'\'s Feed');

		return $this->render('ThreadAndMirrorMoodBoardBundle:Front:feed.html.twig', array(
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
	public function myMoodBoardCircleAction(Request $request)
	{
		// Get the user's profile and their favourite feeds
		$profile = $this->em->getRepository('ThreadAndMirrorProductsBundle:Profile')->findOneByUser($this->getUser()->getId());
		$posts   = $this->em->getRepository('ThreadAndMirrorMoodBoardBundle:Post')->findFromFeedList($profile->getMoodBoardFeeds());

		// Set the dynamic page values
		$this->page->setTitle($this->getUser()->getFullname().'\'s MoodBoard Circle');
		$this->page->setWindowTitle($this->getUser()->getFullname().'\'s MoodBoard Circle');

		return $this->render('ThreadAndMirrorMoodBoardBundle:Front:myCircle.html.twig', array(
			'posts' 		=> $posts,
			'page'			=> $this->page,
		));
	}
}
