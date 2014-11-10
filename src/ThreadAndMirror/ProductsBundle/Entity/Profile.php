<?php
namespace ThreadAndMirror\ProductsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/** 
 * @ORM\Table(name="tam_profile")
 * @ORM\Entity()
 */
class Profile
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
    protected $user;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    protected $filters;

    /** 
     * @ORM\Column(type="array", nullable=true)
     */
    protected $socialFeeds = array();

    /** 
     * @ORM\Column(type="boolean")
     */
    protected $deleted = false; 

    public function __construct($user)
    {
        $this->user = $user->getId();
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
     * Set user
     *
     * @param integer $user
     * @return Profile
     */
    public function setUser($user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return integer 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set socialFeeds
     *
     * @param array $socialFeeds
     * @return Profile
     */
    public function setSocialFeeds($socialFeeds)
    {
        $this->socialFeeds = $socialFeeds;
    
        return $this;
    }

    /**
     * Get socialFeeds
     *
     * @return array 
     */
    public function getSocialFeeds()
    {
        return $this->socialFeeds;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return Profile
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    
        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean 
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set filters
     *
     * @param boolean $filters
     * @return Profile
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    
        return $this;
    }

    /**
     * Get filters
     *
     * @return string 
     */
    public function getFilters()
    {
        return $this->filters;
    }
}