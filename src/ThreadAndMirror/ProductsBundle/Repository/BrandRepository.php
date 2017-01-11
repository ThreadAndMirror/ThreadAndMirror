<?php

namespace ThreadAndMirror\ProductsBundle\Repository;

use Doctrine\ORM\EntityRepository;

class BrandRepository extends EntityRepository
{
    public function getSomething()
    {
        return 'something';
    }
}