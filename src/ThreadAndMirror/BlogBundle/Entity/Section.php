<?php
namespace ThreadAndMirror\BlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;


/** 
 * @ORM\Entity
 * @ORM\Table(name="stm_blog_section")
 */
class Section
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** 
     * @ORM\Column(type="integer")
     */
    protected $position = 1;

    /** 
     * @ORM\Column(type="integer")
     */
    protected $span = 1;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $height = 210;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $width = 210;

    /** 
     * @ORM\Column(type="integer")
     */
    protected $x = 0;

    /** 
     * @ORM\Column(type="integer")
     */
    protected $y = 0;

	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $pinned = true;

    /** 
     * @ORM\Column(type="integer")
     */
    protected $entity;

    /**
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="sections")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="id")
     */
    protected $post;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $type;

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
     * Set entity
     *
     * @param integer $entity
     * @return Section
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    
        return $this;
    }

    /**
     * Get entity
     *
     * @return integer 
     */
    public function getEntity()
    {
        return $this->entity;
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
     * Set post
     *
     * @param ThreadAndMirror\BlogBundle\Entity\Post $post
     */
    public function setPost(\ThreadAndMirror\BlogBundle\Entity\Post $post)
    {
        $this->post = $post;
    }

    /**
     * Get post
     *
     * @return ThreadAndMirror\BlogBundle\Entity\Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set type
     *
     * @param  $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

	/**
	 * Set span
	 *
	 * @param integer $span
	 * @return Section
	 */
	public function setSpan($span)
	{
		$this->span = $span;

		return $this;
	}

	/**
	 * Get span
	 *
	 * @return integer
	 */
	public function getSpan()
	{
		return $this->span;
	}

    /**
     * Set width
     *
     * @param integer $width
     * @return Section
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return integer 
     */
    public function getWidth()
    {
        return $this->width;
    }

	/**
	 * Set height
	 *
	 * @param integer $height
	 * @return Section
	 */
	public function setHeight($height)
	{
		$this->height = $height;

		return $this;
	}

	/**
	 * Get height
	 *
	 * @return integer
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * Set pinned
	 *
	 * @param boolean $pinned
	 * @return Section
	 */
	public function setPinned($pinned)
	{
		$this->pinned = $pinned;

		return $this;
	}

	/**
	 * Get pinned
	 *
	 * @return boolean
	 */
	public function getPinned()
	{
		return $this->pinned;
	}

    /**
     * Set x
     *
     * @param integer $x
     * @return Section
     */
    public function setX($x)
    {
        $this->x = $x;

        return $this;
    }

    /**
     * Get x
     *
     * @return integer 
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * Set y
     *
     * @param integer $y
     * @return Section
     */
    public function setY($y)
    {
        $this->y = $y;

        return $this;
    }

    /**
     * Get y
     *
     * @return integer 
     */
    public function getY()
    {
        return $this->y;
    }
}
