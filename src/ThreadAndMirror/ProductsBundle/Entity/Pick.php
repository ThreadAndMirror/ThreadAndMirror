<?php
namespace ThreadAndMirror\ProductsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;


/** 
 * @ORM\Table(name="tam_pick")
 * @ORM\Entity()
 */
class Pick
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Wishlist", inversedBy="picks")
     * @ORM\JoinColumn(name="wishlist_id", referencedColumnName="id")
     */
    protected $wishlist;

    /**
     * @ORM\ManyToOne(targetEntity="Outfit", inversedBy="picks")
     * @ORM\JoinColumn(name="outfit_id", referencedColumnName="id")
     */
    protected $outfit;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="picks")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;

    /** 
     * @ORM\Column(type="array", nullable=true)
     */
    protected $sizes = array();

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

    /**
     * Set wishlist
     *
     * @param ThreadAndMirror\ProductsBundle\Entity\Wishlist $wishlist
     */
    public function setWishlist(\ThreadAndMirror\ProductsBundle\Entity\Wishlist $wishlist)
    {
        $this->wishlist = $wishlist;
    }

    /**
     * Get wishlist
     *
     * @return ThreadAndMirror\ProductsBundle\Entity\Wishlist 
     */
    public function getWishlist()
    {
        return $this->wishlist;
    }

    /**
     * Set outfit
     *
     * @param ThreadAndMirror\ProductsBundle\Entity\Outfit $outfit
     */
    public function setOutfit(\ThreadAndMirror\ProductsBundle\Entity\Outfit $outfit)
    {
        $this->outfit = $outfit;
    }

    /**
     * Get outfit
     *
     * @return ThreadAndMirror\ProductsBundle\Entity\Outfit 
     */
    public function getOutfit()
    {
        return $this->outfit;
    }

    /**
     * Set product
     *
     * @param ThreadAndMirror\ProductsBundle\Entity\Product $product
     */
    public function setProduct(\ThreadAndMirror\ProductsBundle\Entity\Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get product
     *
     * @return ThreadAndMirror\ProductsBundle\Entity\Product 
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return Pick
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
     * Set added
     *
     * @param \DateTime $added
     * @return Pick
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
     * @return Pick
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
     * Set sizes
     *
     * @param array $sizes
     * @return Product
     */
    public function setSizes($sizes)
    {
        $this->sizes = $sizes;
    
        return $this;
    }

    /**
     * Get sizes
     *
     * @return array 
     */
    public function getSizes()
    {
        return $this->sizes;
    }
}