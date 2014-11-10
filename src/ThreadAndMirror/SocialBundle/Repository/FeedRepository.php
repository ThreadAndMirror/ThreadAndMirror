<?php

namespace ThreadAndMirror\SocialBundle\Repository;

use Doctrine\ORM\EntityRepository;

class FeedRepository extends EntityRepository
{
	/**
	 * Get all active feeds for the specific social media source
	 *
	 * @param  string 	$source		The social media source we want feeds from
	 * @return array 		   		The resulting Feed entities
	 */
	public function findActiveFeeds($source=null)
	{
		// we need a source!
		if (!$source) {
			return null;
		}

		// find the feeds
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->addSelect('feed');
		$qb->from('ThreadAndMirrorSocialBundle:Feed', 'feed');

		$qb->where('feed.active = :active');
		$qb->andWhere($qb->expr()->isNotNull('feed.'.$source.'Handle'));
		$qb->setParameter('active', '1');

		return $qb->getQuery()->getResult();
	}
}