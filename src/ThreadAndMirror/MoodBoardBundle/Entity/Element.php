<?php
namespace ThreadAndMirror\MoodBoardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;


/** 
 * @ORM\Table(name="tam_moodboard_element")
 * InheritanceType("SINGLE_TABLE")
 * DiscriminatorColumn(name="discriminator", type="string")
 * DiscriminatorMap({
 *      "image"   = "ImageElement", 
 * 		"text"    = "TextElement",
 * 		"product" = "ProductElement"
 * })
 */
class Element
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
	protected $x = 0;

	/** 
	 * @ORM\Column(type="integer")
	 */
	protected $y = 0;

	/** 
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected $scale = 1;

	/** 
	 * @ORM\Column(type="integer")
	 */
	protected $rotation = 0;

	/** 
	 * @ORM\Column(type="integer")
	 */
	protected $position = 1;

	/**
	 * @ORM\ManyToOne(targetEntity="MoodBoard", inversedBy="elements")
	 * @ORM\JoinColumn(name="moodboard_id", referencedColumnName="id")
	 */
	protected $moodboard;

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
     * Set x
     *
     * @param integer $x
     * @return Element
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
     * @return Element
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

    /**
     * Set scale
     *
     * @param string $scale
     * @return Element
     */
    public function setScale($scale)
    {
        $this->scale = $scale;

        return $this;
    }

    /**
     * Get scale
     *
     * @return string 
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * Set rotation
     *
     * @param integer $rotation
     * @return Element
     */
    public function setRotation($rotation)
    {
        $this->rotation = $rotation;

        return $this;
    }

    /**
     * Get rotation
     *
     * @return integer 
     */
    public function getRotation()
    {
        return $this->rotation;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return Element
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
     * Set moodboard
     *
     * @param \ThreadAndMirror\MoodBoardBundle\Entity\MoodBoard $moodboard
     * @return Element
     */
    public function setMoodboard(\ThreadAndMirror\MoodBoardBundle\Entity\MoodBoard $moodboard = null)
    {
        $this->moodboard = $moodboard;

        return $this;
    }

    /**
     * Get moodboard
     *
     * @return \ThreadAndMirror\MoodBoardBundle\Entity\MoodBoard 
     */
    public function getMoodboard()
    {
        return $this->moodboard;
    }
}
