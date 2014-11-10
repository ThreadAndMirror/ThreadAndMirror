<?php
namespace ThreadAndMirror\SocialBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;


/** 
 * @ORM\Entity(repositoryClass="ThreadAndMirror\SocialBundle\Repository\FeedRepository")
 * @ORM\Table(name="tam_social_feed")
 */
class Feed
{
	/** 
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/** 
	 * @ORM\Column(type="string", length=255)
	 */
	protected $name;

	/** 
	 * @ORM\Column(type="string", length=255)
	 */
	protected $slug;

	/** 
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $website;

	/** 
	 * @ORM\Column(type="string", length=255)
	 */
	protected $category;

	/** 
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $instagramHandle;

	/** 
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $instagramId;

	/** 
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $twitterHandle;

	/** 
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $pintrestHandle;

	/** 
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $tumblrHandle;

	/** 
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $facebookHandle;

	/** 
	 * @ORM\Column(type="boolean")
	 */
	protected $moderated = false;

	/** 
	 * @ORM\Column(type="boolean")
	 */
	protected $active = true;

	/**
	 * @ORM\OneToMany(targetEntity="Post", mappedBy="Feed")
	 * @ORM\OrderBy({"added" = "DESC"})
	 */
	protected $posts;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->posts = new \Doctrine\Common\Collections\ArrayCollection();
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
	 * @return Feed
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
	 * @return Feed
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
	 * Set website
	 *
	 * @param string $website
	 * @return Feed
	 */
	public function setWebsite($website)
	{
		$this->website = $website;
	
		return $this;
	}

	/**
	 * Get website
	 *
	 * @return string 
	 */
	public function getWebsite()
	{
		return $this->website;
	}

	/**
	 * Set category
	 *
	 * @param string $category
	 * @return Feed
	 */
	public function setCategory($category)
	{
		$this->category = $category;
	
		return $this;
	}

	/**
	 * Get category
	 *
	 * @return string 
	 */
	public function getCategory()
	{
		return $this->category;
	}

	/**
	 * Set instagramHandle
	 *
	 * @param string $instagramHandle
	 * @return Feed
	 */
	public function setInstagramHandle($instagramHandle)
	{
		$this->instagramHandle = $instagramHandle;
	
		return $this;
	}

	/**
	 * Get instagramHandle
	 *
	 * @return string 
	 */
	public function getInstagramHandle()
	{
		return $this->instagramHandle;
	}

	/**
	 * Set instagramId
	 *
	 * @param integer $instagramId
	 * @return Feed
	 */
	public function setInstagramId($instagramId)
	{
		$this->instagramId = $instagramId;
	
		return $this;
	}

	/**
	 * Get instagramId
	 *
	 * @return integer 
	 */
	public function getInstagramId()
	{
		return $this->instagramId;
	}

	/**
	 * Set twitterHandle
	 *
	 * @param string $twitterHandle
	 * @return Feed
	 */
	public function setTwitterHandle($twitterHandle)
	{
		$this->twitterHandle = $twitterHandle;
	
		return $this;
	}

	/**
	 * Get twitterHandle
	 *
	 * @return string 
	 */
	public function getTwitterHandle()
	{
		return $this->twitterHandle;
	}

	/**
	 * Set pintrestHandle
	 *
	 * @param string $pintrestHandle
	 * @return Feed
	 */
	public function setPintrestHandle($pintrestHandle)
	{
		$this->pintrestHandle = $pintrestHandle;
	
		return $this;
	}

	/**
	 * Get pintrestHandle
	 *
	 * @return string 
	 */
	public function getPintrestHandle()
	{
		return $this->pintrestHandle;
	}

	/**
	 * Set facebookHandle
	 *
	 * @param string $facebookHandle
	 * @return Feed
	 */
	public function setFacebookHandle($facebookHandle)
	{
		$this->facebookHandle = $facebookHandle;
	
		return $this;
	}

	/**
	 * Get facebookHandle
	 *
	 * @return string 
	 */
	public function getFacebookHandle()
	{
		return $this->facebookHandle;
	}

	/**
	 * Set tumblrHandle
	 *
	 * @param string $tumblrHandle
	 * @return Feed
	 */
	public function setTumblrHandle($tumblrHandle)
	{
		$this->tumblrHandle = $tumblrHandle;
	
		return $this;
	}

	/**
	 * Get tumblrHandle
	 *
	 * @return string 
	 */
	public function getTumblrHandle()
	{
		return $this->tumblrHandle;
	}

	/**
	 * Set moderated
	 *
	 * @param boolean $moderated
	 * @return Feed
	 */
	public function setModerated($moderated)
	{
		$this->moderated = $moderated;
	
		return $this;
	}

	/**
	 * Get moderated
	 *
	 * @return boolean 
	 */
	public function getModerated()
	{
		return $this->moderated;
	}

	/**
	 * Set active
	 *
	 * @param boolean $active
	 * @return Feed
	 */
	public function setActive($active)
	{
		$this->active = $active;
	
		return $this;
	}

	/**
	 * Get active
	 *
	 * @return boolean 
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * Add posts
	 *
	 * @param \ThreadAndMirror\SocialBundle\Entity\Post $posts
	 * @return Feed
	 */
	public function addPost(\ThreadAndMirror\SocialBundle\Entity\Post $posts)
	{
		$this->posts[] = $posts;
	
		return $this;
	}

	/**
	 * Remove posts
	 *
	 * @param \ThreadAndMirror\SocialBundle\Entity\Post $posts
	 */
	public function removePost(\ThreadAndMirror\SocialBundle\Entity\Post $posts)
	{
		$this->posts->removeElement($posts);
	}

	/**
	 * Get posts
	 *
	 * @return \Doctrine\Common\Collections\Collection 
	 */
	public function getPosts()
	{
		return $this->posts;
	}
}