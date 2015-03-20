<?php

namespace ThreadAndMirror\MoodBoardBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PostRepository extends EntityRepository
{
	/**
	 * Gets a list IDs (source, not our id) for posts that have already been parsed.
	 *
	 * @return array 	A list of IDs
	 */
	public function getExistingPostIds() 
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->addSelect('post');
		$qb->from('ThreadAndMirrorMoodBoardBundle:Post', 'post');

		// get the products in an array to save on memory
		$results = $qb->getQuery()->getScalarResult();

		// build an array with just sids
		$sids = array();

		foreach ($results as $post) {
			$sids[] = $post['post_sid'];
		}

		return $sids; 
	}

	/**
	 * Gets all posts that belong to a specific category of owner
	 *
	 * @param  string 	$category	The slug of the category to filter by
	 * @param  int 		$limit 		The maximum amount of results to return
	 * @return array 				The resulting Post entities
	 */
	public function findByOwnerCategory($category, $offset=0, $limit=50)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->addSelect('post');
		$qb->from('ThreadAndMirrorMoodBoardBundle:Post', 'post');
		$qb->leftJoin('post.feed', 'owner');		
		
		// get posts who's owner belongs to the specified category
		$qb->where('post.deleted = :deleted');
		$qb->andWhere('owner.category = :category');
		$qb->setParameter('deleted', '0');
		$qb->setParameter('category', $category);

		// order by most recently added
		$qb->orderBy('post.created', 'DESC');	

		return $qb->setMaxResults($limit)->setFirstResult($offset)->getQuery()->getResult();
	}

	/**
	 * Gets all posts that belong to owners in the list of owner ids
	 *
	 * @param  array 	$owners		A list of feed owner ids
	 * @param  int 		$limit 		The maximum amount of results to return
	 * @return array 				The resulting Post entities
	 */
	public function findFromFeedList($owners, $offset=0, $limit=50)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->addSelect('post');
		$qb->from('ThreadAndMirrorMoodBoardBundle:Post', 'post');
		$qb->leftJoin('post.feed', 'owner');		
		
		// get posts who's owner is in the whitelist
		$qb->where('post.deleted = :deleted');
		$qb->andWhere($qb->expr()->in('owner.id', ':owners'));
		$qb->setParameter('deleted', '0');
		$qb->setParameter('owners', $owners);

		// order by most recently added
		$qb->orderBy('post.created', 'DESC');	

		return $qb->setMaxResults($limit)->setFirstResult($offset)->getQuery()->getResult();
	}

}