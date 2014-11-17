<?php
namespace ThreadAndMirror\ProductsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Stems\SocialBundle\Service\Sharer;

/** 
 * @ORM\Table(name="tam_product", indexes={
 *    @ORM\Index(name="brand_index", columns={"brand"}),
 *    @ORM\Index(name="pid_index", columns={"pid"}),
 * })
 * @ORM\Entity(repositoryClass="ThreadAndMirror\ProductsBundle\Repository\ProductRepository")
 *
 */
class Product
{
	/** 
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Shop", inversedBy="products")
	 * @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
	 */
	protected $shop;

	/**
	 * @ORM\ManyToOne(targetEntity="Category", inversedBy="products")
	 * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
	 */
	protected $category;

	/** 
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/** 
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $description;

	/** 
	 * @ORM\Column(type="text", nullable=true, length=512)
	 */
	protected $shortDescription;

	/** 
	 * @ORM\Column(type="string", nullable=true, length=64)
	 */
	protected $brand;

	/** 
	 * @ORM\Column(type="string")
	 */
	protected $pid;

	/** 
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $url;

	/** 
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $affiliateUrl;

	/** 
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $thumbnail;

	/** 
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $image;

	/** 
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $was = 0.00;

	/** 
	 * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	protected $now = 0.00;

	/** 
	 * @ORM\Column(type="boolean")
	 */
	protected $available = true;

	/** 
	 * @ORM\Column(type="array", nullable=true)
	 */
	protected $availableSizes = array();

	/** 
	 * @ORM\Column(type="array", nullable=true)
	 */
	protected $stockedSizes = array();

	/** 
	 * @ORM\Column(type="array", nullable=true)
	 */
	protected $styleWith = array();

	/** 
	 * @ORM\Column(type="boolean")
	 */
	protected $attire = true;

	/** 
	 * @ORM\Column(type="boolean")
	 */
	protected $beauty = false;

	/** 
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $sale = null;

	/** 
	 * @ORM\Column(type="boolean")
	 */
	protected $new = false;

	/** 
	 * @ORM\Column(type="boolean")
	 */
	protected $fullyParsed = false;

	/** 
	 * @ORM\Column(type="boolean")
	 */
	protected $deleted = false;

	/** 
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $expired = null;

	/** 
	 * @ORM\Column(type="datetime")
	 */
	protected $added;

	/** 
	 * @ORM\Column(type="datetime")
	 */
	protected $updated;

	/** 
	 * @ORM\Column(type="datetime")
	 */
	protected $checked;

	/** 
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $feature;

	/**
	 * @ORM\OneToMany(targetEntity="Pick", mappedBy="product")
	 */
	protected $picks; 

	/** 
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $metaKeywords;

	/** 
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $metaDescription;

	public function __construct()
	{
		$this->added   = new \DateTime;
		$this->updated = new \DateTime;
		$this->checked = new \DateTime;
	}

	public function updateFromClone($product)
	{
		$this->name           = $product->getName();
		$this->category       = $product->getCategory();
		$this->url            = $product->getUrl();
		$this->affiliateUrl   = $product->getAffiliateUrl();
		$this->fullyParsed    = $product->getFullyParsed();
		$this->category       = $product->getCategory();
		$this->description    = $product->getDescription();
		$this->thumbnail      = $product->getThumbnail();
		$this->image          = $product->getImage();
		$this->was            = $product->getWas();
		$this->now            = $product->getNow();
		$this->available      = $product->getAvailable();
		$this->sale           = $product->getSale();
		$this->new            = $product->getNew();
		$this->expired        = $product->getExpired();
		$this->availableSizes = $product->getAvailableSizes();
		$this->stockedSizes   = $product->getStockedSizes();
		$this->styleWith   	  = $product->getStyleWith();


		return $this;
	}

	/**
	 * Generate the slug for the product
	 */
	public function getSlug()
	{
		$slug = $this->name;

		// replace non letter or digits by -
		$slug = preg_replace('~[^\\pL\d]+~u', '-', $slug);

		// trim
		$slug = trim($slug, '-');

		// transliterate
		$slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);

		// lowercase
		$slug = strtolower($slug);

		// remove unwanted characters
		$slug = preg_replace('~[^-\w]+~', '', $slug);

		$slug .= '-'.$this->id;

		return $slug;
	}

	/**
	 * Create the social sharer object for this product, if no platform is passed we return a default configuration
	 */
	public function getSharer($platform=null)
	{
		$sharer = new Sharer($platform);

		$sharer->setTitle($this->name);
		$sharer->setText($this->name);
		$sharer->setUrl('http://www.threadandmirror.com/product/'.$this->getSlug());
		$sharer->setImage($this->image);
		$sharer->setTags(array('threadandmirror'));

		if ($platform == 'email') {
			$sharer->setText('I found this on Thread %26 Mirror: '.$sharer->getUrl());
		}

		return $sharer;
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
	 * Set shop
	 *
	 * @param ThreadAndMirror\ProductsBundle\Entity\Shop $shop
	 */
	public function setShop(\ThreadAndMirror\ProductsBundle\Entity\Shop $shop)
	{
		$this->shop = $shop;
	}

	/**
	 * Get shop
	 *
	 * @return ThreadAndMirror\ProductsBundle\Entity\Shop 
	 */
	public function getShop()
	{
		return $this->shop;
	}

	/**
	 * Set category
	 *
	 * @param ThreadAndMirror\ProductsBundle\Entity\Category $category
	 */
	public function setCategory(\ThreadAndMirror\ProductsBundle\Entity\Category $category)
	{
		$this->category = $category;
	}

	/**
	 * Get category
	 *
	 * @return ThreadAndMirror\ProductsBundle\Entity\Category 
	 */
	public function getCategory()
	{
		return $this->category;
	}

	/**
	 * Set name
	 *
	 * @param string $name
	 * @return Product
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
	 * Set description
	 *
	 * @param string $description
	 * @return Product
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
		// Return the short description if there's no long one
		if ($this->description === null) {
			return $this->shortDescription;
		} else {
			// Add p tags if they don't exist, for now.
			if (stristr($this->description, '<p>') !== false) {
				return $this->description;
			} else {
				return '<p>'.$this->description.'<p>';
			}
		}
			
	}

	/**
	 * Set shortDescription
	 *
	 * @param string $shortDescription
	 * @return Product
	 */
	public function setShortDescription($shortDescription)
	{
		$this->shortDescription = $shortDescription;
	
		return $this;
	}

	/**
	 * Get shortDescription
	 *
	 * @return string 
	 */
	public function getShortDescription()
	{
		return $this->shortDescription;
	}

	/**
	 * Get a short description without any markup using the current description html
	 *
	 * @return string 
	 */
	public function getRawDescription()
	{
		// get the last paragraph of text from the description
		$raw = $this->description;
		$raw = explode('<p>', $raw);
		$raw = end($raw);
		$raw = explode('</p>', $raw);
		$raw = reset($raw);

		if (trim($raw)) {
			return $raw;
		} else {
			return $this->name.' at '.$this->getShop()->getName();
		}
	}

	/**
	 * Set pid
	 *
	 * @param string $pid
	 * @return Product
	 */
	public function setPid($pid)
	{
		$this->pid = $pid;
	
		return $this;
	}

	/**
	 * Get pid
	 *
	 * @return string 
	 */
	public function getPid()
	{
		return $this->pid;
	}

	/**
	 * Get the url depending on whether the product has an affiliate link
	 *
	 * @return string 
	 */
	public function getFrontendUrl()
	{
		return $this->affiliateUrl ? $this->affiliateUrl : $this->url;
	}

	/**
	 * Set url
	 *
	 * @param string $url
	 * @return Product
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
	 * Set affiliateUrl
	 *
	 * @param string $affiliateUrl
	 * @return Product
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
	 * Set thumbnail
	 *
	 * @param string $thumbnail
	 * @return Product
	 */
	public function setThumbnail($thumbnail)
	{
		$this->thumbnail = $thumbnail;
	
		return $this;
	}

	/**
	 * Get thumbnail
	 *
	 * @return string 
	 */
	public function getThumbnail()
	{
		return $this->thumbnail;
	}

	/**
	 * Set image
	 *
	 * @param string $image
	 * @return Product
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
	 * Set was
	 *
	 * @param float $was
	 * @return Product
	 */
	public function setWas($was)
	{
		$this->was = $was;
	
		return $this;
	}

	/**
	 * Get was
	 *
	 * @return float 
	 */
	public function getWas()
	{
		return $this->was;
	}

	/**
	 * Set now
	 *
	 * @param float $now
	 * @return Product
	 */
	public function setNow($now)
	{
		$this->now = $now;
	
		return $this;
	}

	/**
	 * Get now
	 *
	 * @return float 
	 */
	public function getNow()
	{
		return $this->now;
	}

	/**
	 * Set available
	 *
	 * @param boolean $available
	 * @return Product
	 */
	public function setAvailable($available)
	{
		$this->available = $available;
	
		return $this;
	}

	/**
	 * Get available
	 *
	 * @return boolean 
	 */
	public function getAvailable()
	{
		return $this->available;
	}

	/**
	 * Set sale
	 *
	 * @param boolean $sale
	 * @return Product
	 */
	public function setSale($sale)
	{
		$this->sale = $sale;
	
		return $this;
	}

	/**
	 * Get sale
	 *
	 * @return DateTime 
	 */
	public function getSale()
	{
		return $this->sale;
	}

	/**
	 * Set new
	 *
	 * @param boolean $new
	 * @return Product
	 */
	public function setNew($new)
	{
		$this->new = $new;
	
		return $this;
	}

	/**
	 * Get new
	 *
	 * @return boolean 
	 */
	public function getNew()
	{
		return $this->new;
	}

	/**
	 * Set fullyParsed
	 *
	 * @param boolean $fullyParsed
	 * @return Product
	 */
	public function setFullyParsed($fullyParsed)
	{
		$this->fullyParsed = $fullyParsed;
	
		return $this;
	}

	/**
	 * Get fullyParsed
	 *
	 * @return boolean 
	 */
	public function getFullyParsed()
	{
		return $this->fullyParsed;
	}

	/**
	 * Set deleted
	 *
	 * @param boolean $deleted
	 * @return Product
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
	 * Set expired
	 *
	 * @param datetime $expired
	 * @return Product
	 */
	public function setExpired($expired)
	{
		$this->expired = $expired;
	
		return $this;
	}

	/**
	 * Get expired
	 *
	 * @return datetime 
	 */
	public function getExpired()
	{
		return $this->expired;
	}

	/**
	 * Set added
	 *
	 * @param \DateTime $added
	 * @return Product
	 */
	public function setAdded($added)
	{
		$this->added = $added;
	
		return $this;
	}

	/**
	 * Get added
	 *
	 * @return \DateTime 
	 */
	public function getAdded()
	{
		return $this->added;
	}

	/**
	 * Set updated
	 *
	 * @param \DateTime $updated
	 * @return Product
	 */
	public function setUpdated($updated)
	{
		$this->updated = $updated;
	
		return $this;
	}

	/**
	 * Get updated
	 *
	 * @return \DateTime 
	 */
	public function getUpdated()
	{
		return $this->updated;
	}

	/**
	 * Set checked
	 *
	 * @param \DateTime $checked
	 * @return Product
	 */
	public function setChecked($checked)
	{
		$this->checked = $checked;
	
		return $this;
	}

	/**
	 * Get checked
	 *
	 * @return \DateTime 
	 */
	public function getChecked()
	{
		return $this->checked;
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

    /**
     * Set availableSizes
     *
     * @param array $availableSizes
     * @return Product
     */
    public function setAvailableSizes($availableSizes)
    {
        $this->availableSizes = $availableSizes;
    
        return $this;
    }

    /**
     * Get availableSizes
     *
     * @return array 
     */
    public function getAvailableSizes()
    {
        return $this->availableSizes;
    }

    /**
     * Set stockedSizes
     *
     * @param array $stockedSizes
     * @return Product
     */
    public function setStockedSizes($stockedSizes)
    {
        $this->stockedSizes = $stockedSizes;
    
        return $this;
    }

    /**
     * Get stockedSizes
     *
     * @return array 
     */
    public function getStockedSizes()
    {
        return $this->stockedSizes;
    }

    /**
     * Set styleWith
     *
     * @param array $styleWith
     * @return Product
     */
    public function setStyleWith($styleWith)
    {
        $this->styleWith = $styleWith;
    
        return $this;
    }

    /**
     * Get styleWith
     *
     * @return array 
     */
    public function getStyleWith()
    {
        return $this->styleWith;
    }

    /**
     * Set attire
     *
     * @param boolean $attire
     * @return Product
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
     * Set beauty
     *
     * @param boolean $beauty
     * @return Product
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
	 * Set feature
	 *
	 * @param integer $feature
	 * @return Product
	 */
	public function setFeature($feature)
	{
		$this->feature = $feature;
	
		return $this;
	}

	/**
	 * Get feature
	 *
	 * @return integer 
	 */
	public function getFeature()
	{
		return $this->feature;
	}

    /**
	 * Set metaKeywords
	 *
	 * @param string $metaKeywords
	 * @return Product
	 */
	public function setMetaKeywords($metaKeywords)
	{
		$this->metaKeywords = $metaKeywords;
	
		return $this;
	}

	/**
	 * Get metaKeywords
	 *
	 * @return string 
	 */
	public function getMetaKeywords()
	{
		return $this->metaKeywords;
	}

    /**
	 * Set metaDescription
	 *
	 * @param string $metaDescription
	 * @return Product
	 */
	public function setMetaDescription($metaDescription)
	{
		$this->metaDescription = $metaDescription;
	
		return $this;
	}

	/**
	 * Get metaDescription
	 *
	 * @return string 
	 */
	public function getMetaDescription()
	{
		return $this->metaDescription;
	}

    /**
	 * Set brand
	 *
	 * @param string $brand
	 * @return Product
	 */
	public function setBrand($brand)
	{
		$this->brand = $brand;
	
		return $this;
	}

	/**
	 * Get brand
	 *
	 * @return string 
	 */
	public function getBrand()
	{
		return $this->brand;
	}

    /**
     * Remove picks
     *
     * @param \ThreadAndMirror\ProductsBundle\Entity\Pick $picks
     */
    public function removePick(\ThreadAndMirror\ProductsBundle\Entity\Pick $picks)
    {
        $this->picks->removeElement($picks);
    }
}