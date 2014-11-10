<?php
namespace ThreadAndMirror\ProductsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/** 
 * @ORM\Table(name="tam_shop")
 * @ORM\Entity(repositoryClass="ThreadAndMirror\ProductsBundle\Repository\ShopRepository")
 */
class Shop
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /** 
     * @ORM\Column(type="string")
     */
    protected $slug;

    /** 
     * @ORM\Column(type="text")
     */
    protected $description;

    /** 
     * @ORM\Column(type="string")
     */
    protected $url;

    /** 
     * @ORM\Column(type="string")
     */
    protected $logo;

    /** 
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $imageRatio = 1;

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
    protected $attire = false; 

    /** 
     * @ORM\Column(type="boolean")
     */
    protected $beauty = false; 

    /** 
     * @ORM\Column(type="boolean")
     */
    protected $linkshare = false; 

    /** 
     * @ORM\Column(type="boolean")
     */
    protected $hasFeed = false; 

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $feedModified;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $feedParsed;

    /** 
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $affiliateRate = 6.00;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $affiliateId = null; 

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $affiliateName = null; 

    /**
     * @ORM\OneToMany(targetEntity="Product", mappedBy="shop")
     * @ORM\OrderBy({"added" = "DESC"})
     */
    protected $products; 

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
     * Set name
     *
     * @param string $name
     * @return Shop
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Shop
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    
        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Shop
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Shop
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set logo
     *
     * @param string $logo
     * @return Shop
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    
        return $this;
    }

    /**
     * Get logo
     *
     * @return string 
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set imageRatio
     *
     * @param float $imageRatio
     * @return Shop
     */
    public function setImageRatio($imageRatio)
    {
        $this->imageRatio = $imageRatio;
    
        return $this;
    }

    /**
     * Get imageRatio
     *
     * @return float 
     */
    public function getImageRatio()
    {
        return $this->imageRatio;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return Shop
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
     * Set beauty
     *
     * @param boolean $beauty
     * @return Shop
     */
    public function setBeauty($beauty)
    {
        $this->beauty = $beauty;
    
        return $this;
    }

    /**
     * Get beauty
     *
     * @return boolean 
     */
    public function getBeauty()
    {
        return $this->beauty;
    }

    /**
     * Set attire
     *
     * @param boolean $attire
     * @return Shop
     */
    public function setAttire($attire)
    {
        $this->attire = $attire;
    
        return $this;
    }

    /**
     * Get attire
     *
     * @return boolean 
     */
    public function getAttire()
    {
        return $this->attire;
    }

    /**
     * Set linkshare
     *
     * @param boolean $linkshare
     * @return Shop
     */
    public function setLinkshare($linkshare)
    {
        $this->linkshare = $linkshare;
    
        return $this;
    }

    /**
     * Get linkshare
     *
     * @return boolean 
     */
    public function getLinkshare()
    {
        return $this->linkshare;
    }

    /**
     * Set hasFeed
     *
     * @param boolean $hasFeed
     * @return Shop
     */
    public function setHasFeed($hasFeed)
    {
        $this->hasFeed = $hasFeed;
    
        return $this;
    }

    /**
     * Get hasFeed
     *
     * @return boolean 
     */
    public function getHasFeed()
    {
        return $this->hasFeed;
    }

    /**
     * Set affiliateRate
     *
     * @param float $affiliateRate
     * @return Shop
     */
    public function setAffiliateRate($affiliateRate)
    {
        $this->affiliateRate = $affiliateRate;
    
        return $this;
    }

    /**
     * Get affiliateRate
     *
     * @return float 
     */
    public function getAffiliateRate()
    {
        return $this->affiliateRate;
    }

    /**
     * Set affiliateId
     *
     * @param integer $affiliateId
     * @return Shop
     */
    public function setAffiliateId($affiliateId)
    {
        $this->affiliateId = $affiliateId;
    
        return $this;
    }

    /**
     * Get affiliateId
     *
     * @return integer 
     */
    public function getAffiliateId()
    {
        return $this->affiliateId;
    }

    /**
     * Set affiliateName
     *
     * @param integer $affiliateName
     * @return Shop
     */
    public function setAffiliateName($affiliateName)
    {
        $this->affiliateName = $affiliateName;
    
        return $this;
    }

    /**
     * Get affiliateName
     *
     * @return integer 
     */
    public function getAffiliateName()
    {
        return $this->affiliateName;
    }

    /**
     * Set created
     *
     * @param datetime $created
     * @return Shop
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
     * Add product
     *
     * @param ThreadAndMirror\ProductsBundle\Entity\Product $product
     */
    public function addProduct(\ThreadAndMirror\ProductsBundle\Entity\Product $product)
    {
        $this->products[] = $product;
    }

    /**
     * Get products
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Set feedModified
     *
     * @param datetime $feedModified
     * @return Shop
     */
    public function setFeedModified($feedModified)
    {
        $this->feedModified = $feedModified;
    
        return $this;
    }

    /**
     * Get feedModified
     *
     * @return integer 
     */
    public function getFeedModified()
    {
        return $this->feedModified;
    }

    /**
     * Set feedParsed
     *
     * @param datetime $feedParsed
     * @return Shop
     */
    public function setFeedParsed($feedParsed)
    {
        $this->feedParsed = $feedParsed;
    
        return $this;
    }

    /**
     * Get feedParsed
     *
     * @return integer 
     */
    public function getFeedParsed()
    {
        return $this->feedParsed;
    }
}