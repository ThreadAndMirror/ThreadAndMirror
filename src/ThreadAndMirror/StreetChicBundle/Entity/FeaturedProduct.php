<?php

namespace ThreadAndMirror\StreetChicBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;


/** 
 * @ORM\Table(name="tam_streetchic_featuredproduct")
 * @ORM\Entity()
 */
class FeaturedProduct
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Feature", inversedBy="featuredProducts")
     * @ORM\JoinColumn(name="feature_id", referencedColumnName="id")
     */
    protected $feature;

    /** 
     * @ORM\Column(type="boolean")
     */
    protected $deleted = false;

    /** 
     * @ORM\Column(type="datetime")
     */
    protected $added;

    /** 
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    public function __construct()
    {
        $this->added = new \DateTime;
        $this->updated = new \DateTime;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}