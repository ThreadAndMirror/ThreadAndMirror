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
     * @ORM\Column(type="string", nullable=true, length=32)
     */
    protected $name;

    /** 
     * @ORM\Column(type="string", length=32)
     */
    protected $slug;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $slogan;

    /** 
     * @ORM\Column(type="string")
     */
    protected $url;

    /** 
     * @ORM\Column(type="string")
     */
    protected $affiliateUrl;

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
    protected $hasFashion = false;

    /** 
     * @ORM\Column(type="boolean")
     */
    protected $hasBeauty = false;

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
     * @ORM\Column(type="boolean")
     */
    protected $hasCrawler = false;

    /** 
     * @ORM\Column(type="string", nullable=true, length=32)
     */
    protected $serviceName = null; 

    /** 
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    protected $affiliateRate = 6.00;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $affiliateId = null; 

    /** 
     * @ORM\Column(type="string", nullable=true, length=32)
     */
    protected $affiliateName = null; 

    /**
     * @ORM\OneToMany(targetEntity="Product", mappedBy="shop")
     * @ORM\OrderBy({"added" = "DESC"})
     */
    protected $products; 

    /**
     * @ORM\OneToMany(targetEntity="Offer", mappedBy="shop")
     * @ORM\OrderBy({"end" = "DESC"})
     */
    protected $offers;

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
     * Set slogan
     *
     * @param string $slogan
     * @return Shop
     */
    public function setSlogan($slogan)
    {
        $this->slogan = $slogan;
    
        return $this;
    }

    /**
     * Get slogan
     *
     * @return string 
     */
    public function getSlogan()
    {
        return $this->slogan;
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
     * Get frontend url
     *
     * @return string 
     */
    public function getFrontendUrl()
    {
        return $this->affiliateUrl !== null ? $this->affiliateUrl : $this->url;
    }

    /**
     * Set affiliateUrl
     *
     * @param string $affiliateUrl
     * @return Shop
     */
    public function setAffiliateUrl($affiliateUrl)
    {
        $this->affiliateUrl = $affiliateUrl;
    
        return $this;
    }

    /**
     * Get affiliateUrl
     *
     * @return string 
     */
    public function getAffiliateUrl()
    {
        return $this->affiliateUrl;
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
     * Set hasBeauty
     *
     * @param boolean $hasBeauty
     * @return Shop
     */
    public function setHasBeauty($hasBeauty)
    {
        $this->hasBeauty = $hasBeauty;
    
        return $this;
    }

    /**
     * Get hasBeauty
     *
     * @return boolean 
     */
    public function getHasBeauty()
    {
        return $this->hasBeauty;
    }

    /**
     * Set hasFashion
     *
     * @param boolean $hasFashion
     * @return Shop
     */
    public function setHasFashion($hasFashion)
    {
        $this->hasFashion = $hasFashion;
    
        return $this;
    }

    /**
     * Get hasFashion
     *
     * @return boolean 
     */
    public function getHasFashion()
    {
        return $this->hasFashion;
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
     * @param string $affiliateName
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
     * @return string 
     */
    public function getAffiliateName()
    {
        return $this->affiliateName;
    }

    /**
     * Set hasCrawler
     *
     * @param boolean $hasCrawler
     * @return Shop
     */
    public function setHasCrawler($hasCrawler)
    {
        $this->hasCrawler = $hasCrawler;
    
        return $this;
    }

    /**
     * Get hasCrawler
     *
     * @return boolean 
     */
    public function getHasCrawler()
    {
        return $this->hasCrawler;
    }

    /**
     * Does this shop have a crawler set up
     *
     * @return boolean 
     */
    public function isCrawlable()
    {
        return $this->hasCrawler;
    }

    /**
     * Set serviceName
     *
     * @param string $serviceName
     * @return Shop
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
    
        return $this;
    }

    /**
     * Get serviceName
     *
     * @return string 
     */
    public function getServiceName()
    {
        return $this->serviceName;
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
     * Add offer
     *
     * @param ThreadAndMirror\ProductsBundle\Entity\Offer $offer
     */
    public function addOffer(\ThreadAndMirror\ProductsBundle\Entity\Offer $offer)
    {
        $this->offers[] = $offer;
    }

    /**
     * Get offers
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getOffers()
    {
        return $this->offers;
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

    /**
     * Get Updater service name for this shop
     *
     * @return string
     */
    public function getUpdaterName()
    {
        return 'threadandmirror.products.updater.'.$this->serviceName;
    }

    /**
     * Get Crawler service name for this shop
     *
     * @return string
     */
    public function getCrawlerName()
    {
        return 'threadandmirror.products.crawler.'.$this->serviceName;
    }

    /**
     * Get Formatter service name for this shop
     *
     * @return string
     */
    public function getFormatterName()
    {
        return 'threadandmirror.products.formatter.'.$this->serviceName;
    }
}