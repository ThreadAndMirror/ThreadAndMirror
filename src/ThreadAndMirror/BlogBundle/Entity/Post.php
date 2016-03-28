<?php

namespace ThreadAndMirror\BlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;
use Stems\SocialBundle\Service\Sharer;

/** 
 * @ORM\Entity(repositoryClass="ThreadAndMirror\BlogBundle\Repository\PostRepository")
 * @ORM\Table(name="stm_blog_post")
 */
class Post
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @
     */
    protected $id;

    /** 
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected $title;

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $subTitle;

    /** 
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    protected $excerpt;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    protected $content;

    /** 
     * @ORM\Column(type="string")
     * @Gedmo\Slug(fields={"title", "subTitle"}, separator="-")
     */
    protected $slug;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $image;

    /** 
     * @ORM\Column(type="integer")
     */
    protected $author;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $span = 4;

    /** 
     * @ORM\Column(type="integer")
     */
    protected $height = 300;

    /**
     * @ORM\Column(type="string") 
     */
    protected $status = 'Draft';

    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $hideFromWidgets = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $new = true;

    /** 
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /** 
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $published;

    /**
     * @ORM\OneToMany(targetEntity="Section", mappedBy="post")
     * @ORM\OrderBy({"y" = "ASC", "x" = "ASC"})
     */
    protected $sections; 

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="post")
     */
    protected $comments; 

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $metaTitle;

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $metaKeywords;

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $metaDescription;

	/**
	 * @ORM\ManyToOne(targetEntity="Category", inversedBy="posts")
	 * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
	 */
	protected $category;

    public function __construct()
    {
        $this->title = 'New Post';
        $this->created = new \DateTime;
        $this->updated = new \DateTime;
    }

    /**
     * Create the social sharer object for this post, if no platform is passed we return a default configuration
     *
     * @param  string   $platform   The social media platform to generate the sharer for
     * @return Sharer               The generated sharer
     */
    public function getSharer($platform=null)
    {
        $sharer = new Sharer($platform);

        $sharer->setTitle($this->title.' - '.$this->subTitle);
        $sharer->setText($this->title.' - '.$this->subTitle);
        $sharer->setUrl('http://www.threadandmirror.com/blog/'.$this->slug);
        $sharer->setImage('http://www.threadandmirror.com/'.$this->image);
        $sharer->setTags(array('threadandmirror'));

        return $sharer;
    }

    /** 
     * Get all comments that are not soft deleted
     *
     * @return array                A collection of valid comments
     */
    public function getUndeletedComments()
    {
        // Strip any comments that are deleted
        $comments = array_filter($this->getComments()->toArray(), function($comment) {
            if ($comment->getDeleted()) {
                return false;
            } else {
                return true;
            }
        });

        return $comments;
    }

    /** 
     * Get all comments that have been moderated and not soft deleted
     *
     * @return array                A collection of valid comments
     */
    public function getModeratedComments()
    {
        // Strip any comments that are deleted or unmoderated
        $comments = array_filter($this->getComments()->toArray(), function($comment) {
            if ($comment->getDeleted() || !$comment->getModerated()) {
                return false;
            } else {
                return true;
            }
        });

        return $comments;
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
     * Set title
     *
     * @param string $title
     * @return Post
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set subTitle
     *
     * @param string $subTitle
     * @return Post
     */
    public function setSubTitle($subTitle)
    {
        $this->subTitle = $subTitle;
    
        return $this;
    }

    /**
     * Get subTitle
     *
     * @return string 
     */
    public function getSubTitle()
    {
        return $this->subTitle;
    }

    /**
     * Set excerpt
     *
     * @param string $excerpt
     * @return Post
     */
    public function setExcerpt($excerpt)
    {
        $this->excerpt = $excerpt;
    
        return $this;
    }

    /**
     * Get excerpt
     *
     * @return string 
     */
    public function getExcerpt()
    {
        return $this->excerpt;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Post
     */
    public function setContent($content)
    {
        $this->content = $content;
    
        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Post
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
    public function getslug()
    {
        return $this->slug;
    }

    /**
     * Set image
     *
     * @param integer $image
     * @return Post
     */
    public function setImage($image)
    {
        $this->image = $image;
    
        return $this;
    }

    /**
     * Get image
     *
     * @return integer 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set height
     *
     * @param integer $height
     * @return Post
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
	 * Set span
	 *
	 * @param integer $span
	 * @return Post
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
     * Set author
     *
     * @param string $author
     * @return Post
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    
        return $this;
    }

    /**
     * Get author
     *
     * @return string 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Get author
     *
     * @return string 
     */
    public function getAuthorName()
    {
        return $this->author;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Post
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return Post
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
     * Set hideFromWidgets
     *
     * @param boolean $hideFromWidgets
     * @return Post
     */
    public function setHideFromWidgets($hideFromWidgets)
    {
        $this->hideFromWidgets = $hideFromWidgets;
    
        return $this;
    }

    /**
     * Get hideFromWidgets
     *
     * @return boolean 
     */
    public function getHideFromWidgets()
    {
        return $this->hideFromWidgets;
    }

    /**
     * Set new
     *
     * @param boolean $new
     * @return Post
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
     * Set created
     *
     * @param datetime $created
     * @return Post
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return integer 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param datetime $updated
     * @return Post
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    
        return $this;
    }

    /**
     * Get updated
     *
     * @return integer 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set published
     *
     * @param datetime $published
     * @return Post
     */
    public function setPublished($published)
    {
        $this->published = $published;
    
        return $this;
    }

    /**
     * Get published
     *
     * @return integer 
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Add section
     *
     * @param ThreadAndMirror\BlogBundle\Entity\Section $section
     */
    public function addSection(\ThreadAndMirror\BlogBundle\Entity\Section $section)
    {
        $this->sections[] = $section;
    }

    /**
     * Get sections
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * Add comment
     *
     * @param ThreadAndMirror\BlogBundle\Entity\Comment $comment
     */
    public function addComment(\ThreadAndMirror\BlogBundle\Entity\Comment $comment)
    {
        $this->comments[] = $comment;
    }

    /**
     * Get comments
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set metaTitle
     *
     * @param string $metaTitle
     * @return Post
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;
    
        return $this;
    }

    /**
     * Get metaTitle
     *
     * @return string 
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * Set metaKeywords
     *
     * @param string $metaKeywords
     * @return Post
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
     * @return Post
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
	 * Set category
	 *
	 * @param ThreadAndMirror\BlogBundle\Entity\Category $category
	 */
	public function setCategory(\ThreadAndMirror\BlogBundle\Entity\Category $category)
	{
		$this->category = $category;
	}

	/**
	 * Get category
	 *
	 * @return ThreadAndMirror\BlogBundle\Entity\Category
	 */
	public function getCategory()
	{
		return $this->category;
	}
}