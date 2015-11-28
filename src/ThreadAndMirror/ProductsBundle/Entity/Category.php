<?php

namespace ThreadAndMirror\ProductsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use ThreadAndMirror\ProductsBundle\Util\Slugifier;
use ThreadAndMirror\ProductsBundle\Util\StringSanitizer;

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

	/**
	 * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent = null;

	/**
	 * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
	 */
	protected $children;

	public function __construct()
    {
        $this->products = new ArrayCollection();
	    $this->children = new ArrayCollection();
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
     * @param Product $products
     * @return Category
     */
    public function addProduct(Product $products)
    {
        $this->products[] = $products;

        return $this;
    }

    /**
     * Remove products
     *
     * @param Product $products
     */
    public function removeProduct(Product $products)
    {
        $this->products->removeElement($products);
    }

    /**
     * Get products
     *
     * @return ArrayCollection
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

	/**
	 * @return Category
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * @param  Category     $parent
	 */
	public function setParent(Category $parent)
	{
		$this->parent = $parent;
	}

	/**
	 * Add products
	 *
	 * @param  Category     $child
	 * @return Category
	 */
	public function addChild(Category $child)
	{
		$this->children[] = $child;

		return $this;
	}

	/**
	 * Remove products
	 *
	 * @param  Category     $child
	 */
	public function removeChild(Category $child)
	{
		$this->children->removeElement($child);
	}

	/**
	 * Get products
	 *
	 * @return ArrayCollection
	 */
	public function getChildren()
	{
		return $this->children;
	}

	/**
	 * Get the brand data in json format
	 *
	 * @return string|array
	 */
	public function getJson($encoded = false)
	{
		$json = [
			'id'                => $this->id,
			'name'              => $this->name,
			'slug'              => $this->slug,
			'area'              => $this->area,
			'affiliateWindowId' => $this->affiliateWindowId,
			'parent'            => $this->parent
		];

		return $encoded ? json_encode($json) : $json;
	}
}
