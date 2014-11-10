<?php
namespace ThreadAndMirror\SocialBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;


/** 
 * @ORM\Entity(repositoryClass="ThreadAndMirror\SocialBundle\Repository\PostRepository")
 * @ORM\Table(name="tam_social_post")
 */
class Post
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
	protected $source;

	/** 
	 * @ORM\Column(type="string", length=255)
	 */
	protected $sid;

	/** 
	 * @ORM\Column(type="text", length=255, nullable=true)
	 */
	protected $title;

	/** 
	 * @ORM\Column(type="text", length=255, nullable=true)
	 */
	protected $modifiedTitle;

	/** 
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $caption;

	/** 
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $modifiedCaption;

	/** 
	 * @ORM\Column(type="string")
	 */
	protected $url;

	/** 
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $image;

    /** 
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected $imageRatio = 1;

	/** 
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $thumbnail;

	/** 
	 * @ORM\Column(type="array", nullable=true)
	 */
	protected $tags = array();

	/** 
	 * @ORM\Column(type="datetime")
	 */
	protected $moderated = null;

	/** 
	 * @ORM\Column(type="datetime")
	 */
	protected $created;

	/** 
	 * @ORM\Column(type="datetime")
	 */
	protected $added;

	/** 
	 * @ORM\Column(type="boolean")
	 */
	protected $deleted = false;

	/**
	 * @ORM\ManyToOne(targetEntity="Feed", inversedBy="posts")
	 * @ORM\JoinColumn(name="feed_id", referencedColumnName="id")
	 */
	protected $feed;

	public function __construct($type=null, $data=null)
	{
		$this->added = new \DateTime();
		$this->moderated = new \DateTime();

		// Build the post using type data if passed in
		if ($type && $data) {

			$this->source = $type;

			switch ($type)
			{
				case 'instagram':
					$this->sid       = $data->id;
					$this->image     = $data->images->standard_resolution->url;
					$this->thumbnail = $data->images->thumbnail->url;
					$this->url       = $data->link;
					$this->tags      = $data->tags;
					$this->created   = new \DateTime('@'.$data->created_time);

					// Caption is optional apparently!
					if ($data->caption) {
						$this->title     = $data->caption->text;
						$this->caption   = $data->caption->text;
					}
			
					break;

				case 'twitter':
					$this->sid       = $data->id;
					$this->title     = $data->text;
					$this->caption   = $data->text;
					$this->url       = 'http://twitter.com/'.$data->user->screen_name.'/status/'.$data->id_str;
					$this->created   = new \DateTime($data->created_at);

					// Loops through to get the media to get the image, if there is one
					if (property_exists($data->entities, 'media')) {
						foreach ($data->entities->media as $media) {
							if ($media->type == 'photo') {
								$this->image      = $media->media_url;
								$this->thumbnail  = $media->media_url;
								$this->imageRatio = $media->sizes->medium->w / $media->sizes->medium->h;
							}
						};
					}

					// Loops through to get the hashtags
					if (property_exists($data->entities, 'hashtags')) {
						foreach ($data->entities->hashtags as $tag) {
							$this->tags[] = $tag->text;
						};
					}
	
					break;

					case 'tumblr':
					$this->sid       = $data->id;
					$this->title     = strip_tags($data->caption);
					$this->caption   = strip_tags($data->caption);
					$this->url       = $data->post_url;
					$this->created   = new \DateTime($data->date);

					// Loops through to get the photos to get the image, if there is one
					if (property_exists($data, 'photos')) {
						$this->image      = $data->photos[0]->original_size->url;
						$this->thumbnail  = $data->photos[0]->original_size->url;
					}

					// Loops through to get the tags
					if (property_exists($data, 'tags')) {
						foreach ($data->tags as $tag) {
							$this->tags[] = $tag;
						};
					}
	
					break;

				case 'pintrest':
					# code...
					break;
			}
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
	 * Set source
	 *
	 * @param string $source
	 * @return Post
	 */
	public function setSource($source)
	{
		$this->source = $source;
	
		return $this;
	}

	/**
	 * Get source
	 *
	 * @return string 
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * Set sid
	 *
	 * @param integer $sid
	 * @return Post
	 */
	public function setSid($sid)
	{
		$this->sid = $sid;
	
		return $this;
	}

	/**
	 * Get sid
	 *
	 * @return integer 
	 */
	public function getSid()
	{
		return $this->sid;
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
	 * Set modifiedTitle
	 *
	 * @param string $modifiedTitle
	 * @return Post
	 */
	public function setModifiedTitle($modifiedTitle)
	{
		$this->modifiedTitle = $modifiedTitle;
	
		return $this;
	}

	/**
	 * Get modifiedTitle
	 *
	 * @return string 
	 */
	public function getModifiedTitle()
	{
		return $this->modifiedTitle;
	}

	/**
	 * Set caption
	 *
	 * @param string $caption
	 * @return Post
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
	 * Set modifiedCaption
	 *
	 * @param string $modifiedCaption
	 * @return Post
	 */
	public function setModifiedCaption($modifiedCaption)
	{
		$this->modifiedCaption = $modifiedCaption;
	
		return $this;
	}

	/**
	 * Get modifiedCaption
	 *
	 * @return string 
	 */
	public function getModifiedCaption()
	{
		return $this->modifiedCaption;
	}

	/**
	 * Set url
	 *
	 * @param string $url
	 * @return Post
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
	 * @return string 
	 */
	public function getImage()
	{
		return $this->image;
	}

	/**
	 * Set imageRatio
	 *
	 * @param float $imageRatio
	 * @return Post
	 */
	public function setImageRatio($imageRatio)
	{
		$this->imageRatio = $imageRatio;
	
		return $this;
	}

	/**
	 * Get imageRatio
	 *
	 * @return float 
	 */
	public function getImageRatio()
	{
		return $this->imageRatio;
	}

	/**
	 * Set thumbnail
	 *
	 * @param string $thumbnail
	 * @return Post
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
	 * Set moderated
	 *
	 * @param \DateTime $moderated
	 * @return Post
	 */
	public function setModerated($moderated)
	{
		$this->moderated = $moderated;
	
		return $this;
	}

	/**
	 * Get moderated
	 *
	 * @return \DateTime 
	 */
	public function getModerated()
	{
		return $this->moderated;
	}

	/**
	 * Set added
	 *
	 * @param \DateTime $added
	 * @return Post
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
	 * Set feed
	 *
	 * @param \ThreadAndMirror\SocialBundle\Entity\Feed $feed
	 * @return Post
	 */
	public function setFeed(\ThreadAndMirror\SocialBundle\Entity\Feed $feed = null)
	{
		$this->feed = $feed;
	
		return $this;
	}

	/**
	 * Get feed
	 *
	 * @return \ThreadAndMirror\SocialBundle\Entity\Feed 
	 */
	public function getFeed()
	{
		return $this->feed;
	}
}