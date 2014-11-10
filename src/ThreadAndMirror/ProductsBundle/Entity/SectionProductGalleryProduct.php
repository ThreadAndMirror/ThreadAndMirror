<?php
namespace ThreadAndMirror\ProductsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/** 
 * @ORM\Entity
 * @ORM\Table(name="tam_products_section_productgalleryproduct")
 */
class SectionProductGalleryProduct
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    protected $heading;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    protected $caption;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    protected $url;

    /** 
     * @ORM\Column(type="text")
     */
    protected $thumbnail;

    /** 
     * @ORM\Column(type="text")
     */
    protected $image;

    /** 
     * @ORM\Column(type="text")
     */
    protected $ratio = 1;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $pid;

     /** 
     * @ORM\Column(type="integer")
     */
    protected $position = 99;

    /**
     * @ORM\ManyToOne(targetEntity="SectionProductGallery", inversedBy="images")
     * @ORM\JoinColumn(name="sectionProductGallery_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $sectionProductGallery;

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
     * Set heading
     *
     * @param string $heading
     * @return Section
     */
    public function setHeading($heading)
    {
        $this->heading = $heading;
    
        return $this;
    }

    /**
     * Get heading
     *
     * @return string 
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * Set caption
     *
     * @param string $caption
     * @return Section
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;
    
        return $this;
    }

    /**
     * Get caption
     *
     * @return string 
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Section
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
     * Set thumbnail
     *
     * @param string $thumbnail
     * @return Section
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
     * @return Section
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
     * Set ratio
     *
     * @param string $ratio
     * @return Section
     */
    public function setRatio($ratio)
    {
        $this->ratio = $ratio;
    
        return $this;
    }

    /**
     * Get ratio
     *
     * @return string 
     */
    public function getRatio()
    {
        return $this->ratio;
    }

    /**
     * Set pid
     *
     * @param integer $pid
     * @return Section
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
     * Set position
     *
     * @param integer $position
     * @return Section
     */
    public function setPosition($position)
    {
        $this->position = $position;
    
        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set sectionProductGallery
     *
     * @param \ThreadAndMirror\ProductsBundle\Entity\SectionProductGallery $sectionProductGallery
     * @return SectionProductGalleryImage
     */
    public function setSectionProductGallery(\ThreadAndMirror\ProductsBundle\Entity\SectionProductGallery $sectionProductGallery = null)
    {
        $this->sectionProductGallery = $sectionProductGallery;
    
        return $this;
    }

    /**
     * Get sectionProductGallery
     *
     * @return \ThreadAndMirror\ProductsBundle\Entity\SectionProductGallery 
     */
    public function getSectionProductGallery()
    {
        return $this->sectionProductGallery;
    }
}