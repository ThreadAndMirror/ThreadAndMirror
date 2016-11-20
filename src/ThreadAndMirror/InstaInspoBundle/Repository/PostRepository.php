<?php

namespace ThreadAndMirror\InstaInspoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ThreadAndMirror\InstaInspoBundle\Entity\Post;

class PostRepository extends EntityRepository
{
    /**
     * @param Post $post
     * @param bool $flush
     */
    public function save(Post $post, $flush = true)
    {
        $this->getEntityManager()->persist($post);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}