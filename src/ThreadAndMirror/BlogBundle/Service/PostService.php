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