<?php

namespace ThreadAndMirror\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ThreadAndMirror\BlogBundle\Entity\Category;
use ThreadAndMirror\BlogBundle\Entity\Post;

class WidgetController extends Controller
{
	/**
	 * Renders the latest blog post
	 */
	public function latestPostAction()
	{
		// Get the latest blog post
		$post = $this->get('threadandmirror.blog.service.post')->getLatestPublishedPostForCategory(Category::ARTICLES);

		return $this->render('ThreadAndMirrorBlogBundle:Widget:latestPost.html.twig', [
			'post' => $post
		]);
	}

	/**
	 * Renders a (unpaginated) list of the most recent posts, defaulting to 5 if no limit is set
	 */
	public function latestPostsSidebarAction($limit = 4)
	{
		// Get the latest blog post
		$posts = $this->get('threadandmirror.blog.service.post')->getPublishedPostsForCategory(Category::ARTICLES, $limit);

		return $this->render('ThreadAndMirrorBlogBundle:Widget:latestPostsSidebar.html.twig', [
			'posts' => $posts
		]);
	}

	/**
	 * Renders a specific blog post
	 */
	public function featurePostAction($id)
	{
		// Get the blog post
		$post = $this->get('threadandmirror.blog.service.post')->getPost($id);

		return $this->render('ThreadAndMirrorBlogBundle:Widget:latestPost.html.twig', [
			'post' => $post
		]);
	}

	/**
	 * Renders a blog post that features a product
	 */
	public function featuredInAction($id)
	{
		// Get the blog post
		$post = $this->get('threadandmirror.blog.service.post')->getPost($id);

		return $this->render('ThreadAndMirrorBlogBundle:Widget:featuredIn.html.twig', [
			'post' => $post
		]);
	}

	/**
	 * Renders the latest blog posts for the feature block
	 */
	public function homepageFeatureAction()
	{
		// Get the latest blog post
		$posts = $this->get('threadandmirror.blog.service.post')->getPublishedPostsForCategory(Category::ARTICLES, 5);

		return $this->render('ThreadAndMirrorBlogBundle:Widget:homepageFeature.html.twig', [
			'posts' => $posts
		]);
	}

	/**
	 * Render a block that contains CTAs for next and previous posts in the category of the given article
	 *
	 * @param Post      $post
	 * @param string    $route
	 *
	 * @Template()
	 */
	public function nextAndPreviousPostsAction(Post $post, $route)
	{
		$postService = $this->get('threadandmirror.blog.service.post');

		$next = $postService->getNextPostInCategory($post);
		$prev = $postService->getPreviousPostInCategory($post);

		return [
			'next'  => $next,
			'prev'  => $prev,
			'route' => $route
		];
	}
}
