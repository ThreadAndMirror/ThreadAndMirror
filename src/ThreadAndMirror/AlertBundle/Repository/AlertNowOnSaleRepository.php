<?php

namespace ThreadAndMirror\AlertBundle\Repository;

use Doctrine\ORM\EntityRepository,
	ThreadAndMirror\AlertBundle\ArchiveNowOnSale;

class AlertNowOnSaleRepository extends EntityRepository
{
	/**
	 * All alerts that have been processed
	 */
	public function getProcessed() 
	{
		// get the alerts with a processed date
		$qb = $this->getEntityManager()->createQueryBuilder();

		$qb->addSelect('alert');
		$qb->from('ThreadAndMirrorAlertBundle:AlertNowOnSale', 'alert');
		$qb->where($qb->expr()->isNotNull('alert.processed'));

		$results = $qb->getQuery()->getResult();

		return $results; 
	}
}