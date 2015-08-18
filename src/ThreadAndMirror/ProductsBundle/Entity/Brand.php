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
 * @ORM\Entity(repositoryClass="ThreadAndMirror\ProductsBundle\Repository\BrandRepository")
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
     * @ORM\Column(type="string", length=63)
     */
    protected $name;

	/**
	 * @ORM\Column(type="string", length=511, nullable=true)
	 */
	protected $description;

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

	/**
	 * @ORM\Column(type="array")
	 */
	protected $aliases = [];

    public function __construct($name = null)
    {
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();

	    if ($name !== null) {
		    $this->name = $name;
	    }
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
	 * What the brands slug is expected to be like, based on it's name
	 *
	 * @return string
	 */
	public function guessSlug()
	{
		$text = $this->getName();

		// replace non letter or digits by -
		$text = preg_replace('~[^\\pL\d]+~u', '-', $text);

		// trim
		$text = trim($text, '-');

		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		// lowercase
		$text = strtolower($text);

		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);

		return $text;
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

	/**
	 * Get aliases
	 *
	 * @return array
	 */
	public function getAliases()
	{
		return $this->aliases;
	}

	/**
	 * Get the brand data in json format
	 *
	 * @return string|array
	 */
	public function getJson($encoded = false)
	{
		$json = [
			'id'          => $this->id,
			'name'        => $this->name,
			'description' => $this->description,
			'hasFashion'  => $this->hasFashion,
			'hasBeauty'   => $this->hasBeauty
		];

		return $encoded ? json_encode($json) : $json;
	}
}
