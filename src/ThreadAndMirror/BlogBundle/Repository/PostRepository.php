<?php

namespace ThreadAndMirror\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Snc\RedisBundle\Doctrine\Cache\RedisCache;
use Redis;

class PostRepository extends EntityRepository
{
	/** 
	 * Get the latest posts
	 *
	 * @param  integer 	$limit
	 * @param  integer 	$offset
	 * @param  boolean 	$forWidget
	 * @return array 					The resulting posts
	 */
	public function findLatest($limit = 5, $offset = 0, $forWidget = false) 
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->addSelect('post');
		$qb->from('ThreadAndMirrorBlogBundle:Post', 'post');
		
		// Set parameters
		$qb->where('post.deleted = :deleted');
		$qb->andWhere('post.status = :status');
		$qb->setParameter('deleted', '0');
		$qb->setParameter('status', 'Published');

		// Filter those hidden for widget, if specified
		if ($forWidget) {
			$qb->andWhere('post.hideFromWidgets = :hideFromWidgets');
			$qb->setParameter('hideFromWidgets', false);
		}

		// Order by most recently publishsed
		$qb->orderBy('post.created', 'DESC');

		// Execute the query
		return $qb
			->setMaxResults($limit)
			->setFirstResult($offset)
			->getQuery()
			// ->setResultCacheDriver($redis = $this->loadRedis())
			// ->setResultCacheLifetime(86400)
			->getResult();
	}

	/**
	 * Get the latest published posts for a category
	 *
	 * @param  string 	$category       Category slug
	 * @param  integer 	$limit
	 * @param  integer 	$offset
	 * @return Post[]
	 */
	public function findPublishedPostsByCategory($category, $limit = 5, $offset = 0)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->addSelect('post');
		$qb->from('ThreadAndMirrorBlogBundle:Post', 'post');
		$qb->innerJoin('post.category', 'category');

		// Set parameters
		$qb->where('post.deleted = :deleted');
		$qb->andWhere('post.status = :status');
		$qb->andWhere('category.slug = :category');
		$qb->setParameter('deleted', '0');
		$qb->setParameter('status', 'Published');
		$qb->setParameter('category', $category);

		// Order by most recently publishsed
		$qb->orderBy('post.created', 'DESC');

		// Execute the query
		return $qb
			->setMaxResults($limit)
			->setFirstResult($offset)
			->getQuery()
			// ->setResultCacheDriver($redis = $this->loadRedis())
			// ->setResultCacheLifetime(86400)
			->getResult();
	}

	/**
	 * Get a published post in a specific category
	 *
	 * @param  string 	$slug           Post slug
	 * @param  string 	$category       Category slug
	 * @return Post
	 */
	public function findPublishedPost($slug, $category = null)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->addSelect('post');
		$qb->from('ThreadAndMirrorBlogBundle:Post', 'post');
		$qb->innerJoin('post.category', 'category');

		// Set parameters
		$qb->where('post.deleted = :deleted');
		$qb->andWhere('post.status = :status');
		$qb->andWhere('post.slug = :slug');
		$qb->setParameter('deleted', '0');
		$qb->setParameter('status', 'Published');

		$qb->setParameter('slug', $slug);

		if ($category != null) {
			$qb->andWhere('category.slug = :category');
			$qb->setParameter('category', $category);
		}

		// Execute the query
		return $qb
			->getQuery()
			// ->setResultCacheDriver($redis = $this->loadRedis())
			// ->setResultCacheLifetime(86400)
			->getSingleResult();
	}

	public function findLatestForWidget($limit, $offset = 0)
	{
		return $this->findLatest($limit, $offset, true);
	}

	protected function loadRedis() 
	{
		$cache = new RedisCache();
		$cache->setRedis(new Redis());

		return $cache;
	}
}