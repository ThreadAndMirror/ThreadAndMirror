<?php
namespace ThreadAndMirror\EditorsPicksBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Stems\SocialBundle\Service\Sharer;

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
     * @ORM\Column(type="text", length=64)
     * @Gedmo\Slug(fields={"header"})
     */
    protected $slug;

    /** 
     * @ORM\Column(type="string")
     * @Assert\NotBlank(groups={"full"})
     */
    protected $header;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    protected $caption;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $image;

    /** 
     * @ORM\Column(type="string", length=32)
     */
    protected $layout = 'outfits';

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
     * @Gedmo\Timestampable(on="create")
     */
    protected $created;

    /** 
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updated;

    /** 
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $published;

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
     * @ORM\OneToMany(targetEntity="Pick", mappedBy="collection", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $picks; 

    public function __construct()
    {
        $this->picks = new ArrayCollection();
    }

    /**
     * Create the social sharer object for this collection
     *
     * @param  string   $platform       The social media platform to generate the sharer for
     * @return Sharer                   The generated sharer
     */
    public function getSharer($platform=null)
    {
        $sharer = new Sharer($platform);

        $sharer->setTitle($this->header);
        $sharer->setText($this->header);
        $sharer->setUrl('http://www.threadandmirror.com/editors-picks/'.$this->slug);
        $sharer->setImage('http://www.threadandmirror.com/'.$this->picks[0]->getImage());
        $sharer->setTags(array('threadandmirror'));

        return $sharer;
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
     * Set slug
     *
     * @param string $slug
     * @return Collection
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
     * Set image
     *
     * @param integer $image
     * @return Collection
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
     * Set metaTitle
     *
     * @param string $metaTitle
     * @return Collection
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
        return $this->metaTitle === null ? $this->header : $this->metaTitle;
    }

    /**
     * Set metaKeywords
     *
     * @param string $metaKeywords
     * @return Collection
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
     * @return Collection
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
     * Add pick
     *
     * @param \ThreadAndMirror\EditorsPicksBundle\Entity\Pick $pick
     * @return ArrayCollection
     */
    public function addPick(\ThreadAndMirror\EditorsPicksBundle\Entity\Pick $pick)
    {
        $this->picks->add($pick);

        $pick->setCollection($this);

        return $this;
    }

    /**
     * Remove pick
     *
     * @param \ThreadAndMirror\EditorsPicksBundle\Entity\Pick $pick
     */
    public function removePick(\ThreadAndMirror\EditorsPicksBundle\Entity\Pick $pick)
    {
        $this->picks->removeElement($pick);
    }

    /**
     * Get picks
     *
     * @return \Doctrine\Common\Collections\ArrayCollection 
     */
    public function getPicks()
    {
        return $this->picks;
    }

    /**
     * Add a pick at a specific collection offset by filling it with dummy picks
     *
     * @param  Pick     $pick
     * @param  integer  $offset
     */
    public function addPickAtOffset($pick, $offset)
    {
        $count = 0;

        while ($count < $offset) {
            $this->addPick(new Pick());
            $count++;
        }

        $this->addPick($pick);
    }
}
