<?php

namespace ThreadAndMirror\BlogBundle\Service;

use ThreadAndMirror\BlogBundle\Entity\Post;
use ThreadAndMirror\BlogBundle\Repository\PostRepository;

class PostService
{
	/**
	 * @var PostRepository
	 */
	protected $repository;

	public function __construct(PostRepository $repository)
	{
		$this->repository = $repository;
	}

	/**
	 * @param int $id
	 *
	 * @return Post
	 */
	public function getPost($id)
	{
		return $this->repository->find($id);
	}

	/**
	 * Get the latest published posts for a category
	 *
	 * @param  string 	$category       Category slug
	 * @param  integer 	$limit
	 * @param  integer 	$offset
	 *
	 * @return Post[]
	 */
	public function getPublishedPostsForCategory($category, $limit = 5, $offset = 0)
	{
		return $this->repository->findPublishedPostsByCategory($category, $limit, $offset);
	}

	/**
	 * Get the latest published posts for a category
	 *
	 * @param  string 	$category       Category slug
	 *
	 * @return Post
	 */
	public function getLatestPublishedPostForCategory($category)
	{
		$posts = $this->getPublishedPostsForCategory($category, 1);

		return reset($posts);
	}

	/**
	 * Get the next post in the given posts category
	 *
	 * @param Post $post
	 *
	 * @return Post
	 */
	public function getNextPostInCategory(Post $post)
	{
		return $this->repository->findNextPostInCategory($post);
	}

	/**
	 * Get the previous post in the given posts category
	 *
	 * @param Post $post
	 *
	 * @return Post
	 */
	public function getPreviousPostInCategory(Post $post)
	{
		return $this->repository->findPreviousPostInCategory($post);
	}
}