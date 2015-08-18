<?php

namespace ThreadAndMirror\ProductsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/** 
 * @ORM\Table(name="tam_category", indexes={
 *    @ORM\Index(name="name_index", columns={"name"}),
 *    @ORM\Index(name="slug_index", columns={"slug"}),
 *    @ORM\Index(name="area_index", columns={"area"}),
 *    @ORM\Index(name="affiliate_window_id_index", columns={"affiliateWindowId"})
 * })
 * @ORM\Entity(repositoryClass="ThreadAndMirror\ProductsBundle\Repository\CategoryRepository")
 */
class Category
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
    protected $name = 'category';

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
     * @ORM\OneToMany(targetEntity="Product", mappedBy="category")
     * @ORM\OrderBy({"added" = "DESC"})
     */
    protected $products; 

    /** 
     * @ORM\Column(type="string", nullable=true, length=32)
     */
    protected $area;

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
     * @return Category
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
     * @return Category
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
     * Set affiliateWindowId
     *
     * @param integer $affiliateWindowId
     * @return Category
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
     * @return Category
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
     * Set area
     *
     * @param string $area
     * @return Category
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return string 
     */
    public function getArea()
    {
        return $this->area;
    }
}
