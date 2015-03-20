<?php

namespace ThreadAndMirror\ProductsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/** 
 * @ORM\Table(name="tam_brand", indexes={
 *    @ORM\Index(name="name_index", columns={"name"}),
 *    @ORM\Index(name="slug_index", columns={"slug"}),
 *    @ORM\Index(name="affiliate_window_id_index", columns={"affiliateWindowId"})
 * })
 * @ORM\Entity()
 */
class Brand
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** 
     * @ORM\Column(type="string")
     */
    protected $name;

    /** 
     * @Gedmo\Slug(fields={"name"}, updatable=true, separator="-")
     * @ORM\Column(type="string")
     */
    protected $slug;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $affiliateWindowId = null; 

    /**
     * @ORM\OneToMany(targetEntity="Product", mappedBy="brand")
     */
    protected $products; 

    /** 
     * @ORM\Column(type="boolean")
     */
    protected $hasFashion = false;

    /** 
     * @ORM\Column(type="boolean")
     */
    protected $hasBeauty = false;

    public function __construct()
    {
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Brand
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
     * @return Brand
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
     * Set hasBeauty
     *
     * @param boolean $hasBeauty
     * @return Brand
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
     * @return Brand
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
     * Set affiliateWindowId
     *
     * @param integer $affiliateWindowId
     * @return Brand
     */
    public function setAffiliateWindowId($affiliateWindowId)
    {
        $this->affiliateWindowId = $affiliateWindowId;

        return $this;
    }

    /**
     * Get affiliateWindowId
     *
     * @return integer 
     */
    public function getAffiliateWindowId()
    {
        return $this->affiliateWindowId;
    }

    /**
     * Add products
     *
     * @param \ThreadAndMirror\ProductsBundle\Entity\Product $products
     * @return Brand
     */
    public function addProduct(\ThreadAndMirror\ProductsBundle\Entity\Product $products)
    {
        $this->products[] = $products;

        return $this;
    }

    /**
     * Remove products
     *
     * @param \ThreadAndMirror\ProductsBundle\Entity\Product $products
     */
    public function removeProduct(\ThreadAndMirror\ProductsBundle\Entity\Product $products)
    {
        $this->products->removeElement($products);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProducts()
    {
        return $this->products;
    }

    
}
