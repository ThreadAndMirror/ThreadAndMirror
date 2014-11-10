<?php

namespace ThreadAndMirror\StreetChicBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/** 
 * @ORM\Table(name="tam_streetchic_feature")
 * @ORM\Entity()
 */
class Feature
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** 
     * @ORM\Column(type="integer")
     */
    protected $owner;

    /** 
     * @ORM\Column(type="string")
     */
    protected $title;

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $caption;

     /** 
     * @ORM\Column(type="array")
     */
    protected $images;

    /** 
     * @ORM\Column(type="string")
     */
    protected $layout = 'default';

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $accreditationName;

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $accreditationCaption;

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $accreditationUrl;

    /** 
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /** 
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $published = null;

    /** 
     * @ORM\Column(type="boolean")
     */
    protected $deleted = false; 

    /**
     * @ORM\OneToMany(targetEntity="FeaturedProduct", mappedBy="feature")
     */
    protected $relatedProducts;

    public function __construct()
    {
        $this->created = new \DateTime;
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