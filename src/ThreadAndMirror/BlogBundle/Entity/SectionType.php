<?php
namespace ThreadAndMirror\BlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;


/** 
 * @ORM\Entity
 * @ORM\Table(name="stm_blog_section_type")
 */
class SectionType
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
    protected $name;

    /** 
     * @ORM\Column(type="string")
     */
    protected $class;

    /** 
     * @ORM\Column(type="boolean")
     */
    protected $enabled = true;

    /**
     * @ORM\OneToMany(targetEntity="Section", mappedBy="type")
     */
    protected $sections; 


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sections = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return SectionType
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
     * Set class
     *
     * @param string $class
     * @return SectionType
     */
    public function setClass($class)
    {
        $this->class = $class;
    
        return $this;
    }

    /**
     * Get class
     *
     * @return string 
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set render
     *
     * @param string $render
     * @return SectionType
     */
    public function setRender($render)
    {
        $this->render = $render;
    
        return $this;
    }

    /**
     * Get render
     *
     * @return string 
     */
    public function getRender()
    {
        return $this->render;
    }

    /**
     * Set update
     *
     * @param string $update
     * @return SectionType
     */
    public function setUpdate($update)
    {
        $this->update = $update;
    
        return $this;
    }

    /**
     * Get update
     *
     * @return string 
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return SectionType
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    
        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean 
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Add sections
     *
     * @param \ThreadAndMirror\BlogBundle\Entity\Section $sections
     * @return SectionType
     */
    public function addSection(\ThreadAndMirror\BlogBundle\Entity\Section $sections)
    {
        $this->sections[] = $sections;
    
        return $this;
    }

    /**
     * Remove sections
     *
     * @param \ThreadAndMirror\BlogBundle\Entity\Section $sections
     */
    public function removeSection(\ThreadAndMirror\BlogBundle\Entity\Section $sections)
    {
        $this->sections->removeElement($sections);
    }

    /**
     * Get sections
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSections()
    {
        return $this->sections;
    }
}