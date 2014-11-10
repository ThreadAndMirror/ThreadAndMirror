<?php

namespace ThreadAndMirror\EditorsPicksBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;


/** 
 * @ORM\Table(name="tam_editorspicks_pick")
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
     * @ORM\Column(type="string")
     */
    protected $designer;

    /** 
     * @ORM\Column(type="string")
     */
    protected $name;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="integer")
     */
    protected $product;

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $url;

    /** 
     * @ORM\Column(type="string")
     */
    protected $image;

    /** 
     * @ORM\Column(type="boolean")
     */
    protected $deleted = false;

    /**
     * @ORM\Column(type="integer")
     */
    protected $author;

    /** 
     * @ORM\Column(type="datetime")
     */
    protected $added;

    /** 
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\ManyToOne(targetEntity="Collection", inversedBy="picks")
     * @ORM\JoinColumn(name="wishlist_id", referencedColumnName="id")
     */
    protected $collection;

    public function __construct($product=null)
    {
        $this->added = new \DateTime;
        $this->updated = new \DateTime;

        if ($product) {
            $this->name        = $product->getName();
            $this->description = 'At '.$product->getShop()->getName();
            $this->url         = $product->getFrontendUrl();
            $this->image       = $product->getImage();
            $this->product     = $product->getId();
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
     * Set designer
     *
     * @param string $designer
     * @return Pick
     */
    public function setDesigner($designer)
    {
        $this->designer = $designer;
    
        return $this;
    }

    /**
     * Get designer
     *
     * @return string 
     */
    public function getDesigner()
    {
        return $this->designer;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Pick
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
     * @return Pick
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
     * Set product
     *
     * @param integer $product
     * @return Pick
     */
    public function setProduct($product)
    {
        $this->product = $product;
    
        return $this;
    }

    /**
     * Get product
     *
     * @return integer 
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Pick
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
     * Set image
     *
     * @param string $image
     * @return Pick
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
     * Set author
     *
     * @param integer $author
     * @return Pick
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    
        return $this;
    }

    /**
     * Get author
     *
     * @return integer 
     */
    public function getAuthor()
    {
        return $this->author;
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
     * Set collection
     *
     * @param ThreadAndMirror\EditorsPicksBundle\Entity\Collection $collection
     */
    public function setCollection(\ThreadAndMirror\EditorsPicksBundle\Entity\Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Get collection
     *
     * @return ThreadAndMirror\EditorsPicksBundle\Entity\Collection 
     */
    public function getCollection()
    {
        return $this->collection;
    }
}