<?php

namespace ThreadAndMirror\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ThreadAndMirror\BlogBundle\Entity\Section;

class SectionRepository extends EntityRepository
{
	/**
	 * @param Section $section
	 */
	public function save(Section $section)
	{
		$this->getEntityManager()->persist($section);
		$this->getEntityManager()->flush();
	}
}