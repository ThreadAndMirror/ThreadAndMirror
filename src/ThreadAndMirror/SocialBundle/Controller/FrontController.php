<?php

namespace ThreadAndMirror\SocialBundle\Controller;

use Stems\CoreBundle\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class FrontController extends BaseFrontController
{
	/**
	 * A combined feed of all latest social posts
	 *
	 * @param  Request $request
	 *
	 * @Route("/social", name="thread_social_front_index")
	 * @Template()
	 */
//	public function indexAction(Request $request)
//	{
//		// Get the social posts for the view
//		$posts = $this->em->getRepository('ThreadAndMirrorSocialBundle:Post')->findBy(['deleted' => false], ['created' => 'DESC'], 30);
//
//		return [
//			'posts'        => $posts,
//			'categories'   => $this->container->getParameterBag()->get('threadandmirror.social.feed_categories'),
//			'loadMorePath' => $this->generateUrl('thread_social_rest_more_posts', ['source' => 'all', 'offset' => 'offset'])
//		];
//	}

	/**
	 * A combined feed of all latest social posts from a specific source
	 *
	 * @param  Request $request
	 * @param  string $source The slug of the feed type to filter by, if any
	 *
	 * @Route("/social/{source}", name="thread_social_front_source")
	 * @Template()
	 */
//	public function sourceAction(Request $request, $source)
//	{
//		// Get the social posts for the view
//		$posts = $this->em->getRepository('ThreadAndMirrorSocialBundle:Post')->findBy(['source' => $source, 'deleted' => false], ['created' => 'DESC'], 30);
//
//		return [
//			'posts'      => $posts,
//			'categories' => $this->container->getParameterBag()->get('threadandmirror.social.feed_categories'),
//			'loadMorePath' => $this->generateUrl('thread_social_rest_more_posts', ['source' => $source, 'offset' => 'offset'])
//		];
//	}

	/**
	 * A combined feed of latest social posts from a specific category
	 *
	 * @param  Request  $request
	 * @param  string   $category   The category of the feed to filter by
	 *
	 * @Route("/social/category/{category}", name="thread_social_front_category")
	 * @Template()
	 */
//	public function categoryAction(Request $request, $category = 'store')
//	{
//		$categories = $this->container->getParameterBag()->get('threadandmirror.social.feed_categories');
//
//		// Check the cateogry is in our allowed list
//		if (!array_key_exists($category, $categories)) {
//			return $this->redirect($this->generateUrl('thread_front_social_index'));
//		}
//
//		// Get the social posts for the view
//		$posts = $this->em->getRepository('ThreadAndMirrorSocialBundle:Post')->findByOwnerCategory($category);
//
//		return [
//			'posts'           => $posts,
//			'categories'      => $categories,
//			'currentCategory' => $category,
//			'loadMorePath'    => $this->generateUrl('thread_social_rest_more_category_posts', ['category' => $category, 'offset' => 'offset'])
//		];
//	}

	/**
	 * All posts for a specific feed
	 *
	 * @param  Request $request
	 * @param  string $slug The slug of the feed
	 */
//	public function feedAction(Request $request, $slug)
//	{
//		$feed = $this->em->getRepository('ThreadAndMirrorSocialBundle:Feed')->findOneBySlug($slug);
//		$categories = $this->container->getParameterBag()->get('threadandmirror.social.feed_categories');
//
//		// Redirect if the feed could not be found
//		if (!$feed) {
//			return $this->redirect($this->generateUrl('thread_front_social_index'));
//		}
//
//		// Get the social posts for the view
//		$posts = $this->em->getRepository('ThreadAndMirrorSocialBundle:Post')->findBy(array('feed' => $feed, 'deleted' => false), array('created' => 'DESC'), 30);
//
//		// Set the dynamic page values
//		$this->page->setTitle($feed->getName() . '\'s Feed');
//		$this->page->setWindowTitle($feed->getName() . '\'s Feed');
//
//		return $this->render('ThreadAndMirrorSocialBundle:Front:feed.html.twig', array(
//			'posts'      => $posts,
//			'page'       => $this->page,
//			'categories' => $categories,
//			'feed'       => $feed,
//		));
//	}

	/**
	 * All posts from feeds a user has added to their favourites
	 *
	 * @param  Request $request
	 */
//	public function mySocialCircleAction(Request $request)
//	{
//		// Get the user's profile and their favourite feeds
//		$profile = $this->em->getRepository('ThreadAndMirrorProductsBundle:Profile')->findOneByUser($this->getUser()->getId());
//		$posts = $this->em->getRepository('ThreadAndMirrorSocialBundle:Post')->findFromFeedList($profile->getSocialFeeds());
//
//		// Set the dynamic page values
//		$this->page->setTitle($this->getUser()->getFullname() . '\'s Social Circle');
//		$this->page->setWindowTitle($this->getUser()->getFullname() . '\'s Social Circle');
//
//		return $this->render('ThreadAndMirrorSocialBundle:Front:myCircle.html.twig', array(
//			'posts' => $posts,
//			'page'  => $this->page,
//		));
//	}
}
