<?php
namespace ThreadAndMirror\EditorsPicksBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/** 
 * @ORM\Table(name="tam_editorspicks_collection")
 * @ORM\Entity()
 */
class Collection
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
    protected $owner;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    protected $header;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    protected $caption;

    /** 
     * @ORM\Column(type="integer")
     */
    protected $layout = 1;

    /** 
     * @ORM\Column(type="text")
     */
    protected $status = 'Draft';

    /** 
     * @ORM\Column(type="boolean")
     */
    protected $deleted = false; 

    /** 
     * @ORM\Column(type="boolean")
     */
    protected $monitored = true; 

    /** 
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /** 
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /** 
     * @ORM\Column(type="datetime")
     */
    protected $published;

    /**
     * @ORM\OneToMany(targetEntity="Pick", mappedBy="collection")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $picks; 

    public function __construct($user)
    {
        $this->owner = $user->getId();
        $this->created = new \DateTime;
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
     * Set owner
     *
     * @param integer $owner
     * @return Collection
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    
        return $this;
    }

    /**
     * Get owner
     *
     * @return integer 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set layout
     *
     * @param integer $layout
     * @return Collection
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    
        return $this;
    }

    /**
     * Get layout
     *
     * @return integer 
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set header
     *
     * @param string $header
     * @return Collection
     */
    public function setHeader($header)
    {
        $this->header = $header;
    
        return $this;
    }

    /**
     * Get header
     *
     * @return string 
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Set caption
     *
     * @param string $caption
     * @return Collection
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
     * Set status
     *
     * @param string $status
     * @return Collection
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
     * @return Collection
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    
        return $this;
    }

    /**
     * Get deleted
     *
     * @return string 
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set monitored
     *
     * @param boolean $monitored
     * @return Collection
     */
    public function setMonitored($monitored)
    {
        $this->monitored = $monitored;
    
        return $this;
    }

    /**
     * Get monitored
     *
     * @return string 
     */
    public function getMonitored()
    {
        return $this->monitored;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Collection
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Collection
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
     * Set published
     *
     * @param \DateTime $published
     * @return Collection
     */
    public function setPublished($published)
    {
        $this->published = $published;
    
        return $this;
    }

    /**
     * Get published
     *
     * @return \DateTime 
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Add pick
     *
     * @param ThreadAndMirror\EditorsPicksBundle\Entity\Pick $pick
     */
    public function addPick(\ThreadAndMirror\EditorsPicksBundle\Entity\Pick $pick)
    {
        $this->picks[] = $pick;
    }

    /**
     * Get picks
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getPicks()
    {
        return $this->picks;
    }
}