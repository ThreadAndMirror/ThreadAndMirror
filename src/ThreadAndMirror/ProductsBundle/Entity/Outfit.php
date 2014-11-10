<?php
namespace ThreadAndMirror\ProductsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/** 
 * @ORM\Table(name="tam_outfit")
 * @ORM\Entity()
 */
class Outfit
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
     * @ORM\Column(type="string", nullable=true)
     */
    protected $image;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $featured = null;

    /** 
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /** 
     * @ORM\Column(type="boolean")
     */
    protected $deleted = false; 

    /**
     * @ORM\OneToMany(targetEntity="Pick", mappedBy="outfit")
     * @ORM\OrderBy({"added" = "DESC"})
     */
    protected $picks; 

    public function __construct()
    {
        $this->created = new \DateTime;
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

    /**
     * Set owner
     *
     * @param integer $owner
     * @return Outfit
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    
        return $this;
    }

    /**
     * Get owner
     *
     * @return integer 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Outfit
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set caption
     *
     * @param string $caption
     * @return Outfit
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;
    
        return $this;
    }

    /**
     * Get caption
     *
     * @return string 
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Outfit
     */
    public function setImage($image)
    {
        $this->image = $image;
    
        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return Outfit
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    
        return $this;
    }

    /**
     * Get deleted
     *
     * @return string 
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set featured
     *
     * @param datetime $featured
     * @return Outfit
     */
    public function setFeatured($featured)
    {
        $this->featured = $featured;
    
        return $this;
    }

    /**
     * Get featured
     *
     * @return datetime 
     */
    public function getFeatured()
    {
        return $this->featured;
    }

    /**
     * Set created
     *
     * @param datetime $created
     * @return Outfit
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return datetime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Add pick
     *
     * @param ThreadAndMirror\ProductsBundle\Entity\Pick $pick
     */
    public function addPick(\ThreadAndMirror\ProductsBundle\Entity\Pick $pick)
    {
        $this->picks[] = $pick;
    }

    /**
     * Get picks
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getPicks()
    {
        return $this->picks;
    }
}