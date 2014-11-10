<?php
namespace ThreadAndMirror\ProductsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/** 
 * @ORM\Table(name="tam_wishlist")
 * @ORM\Entity()
 */
class Wishlist
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
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /** 
     * @ORM\Column(type="boolean")
     */
    protected $deleted = false; 

    /** 
     * @ORM\Column(type="boolean")
     */
    protected $monitored = true; 

    /**
     * @ORM\OneToMany(targetEntity="Pick", mappedBy="wishlist")
     * @ORM\OrderBy({"added" = "DESC"})
     */
    protected $picks; 

    public function __construct($user)
    {
        $this->owner = $user->getId();
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
     * @return Wishlist
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
     * Set deleted
     *
     * @param boolean $deleted
     * @return Wishlist
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
     * Set monitored
     *
     * @param boolean $monitored
     * @return Wishlist
     */
    public function setMonitored($monitored)
    {
        $this->monitored = $monitored;
    
        return $this;
    }

    /**
     * Get monitored
     *
     * @return string 
     */
    public function getMonitored()
    {
        return $this->monitored;
    }

    /**
     * Set created
     *
     * @param datetime $created
     * @return Wishlist
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return integer 
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