<?php
namespace ThreadAndMirror\MoodBoardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/** 
 */
class ProductElement extends Element
{
	/** 
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected $name;

    /** 
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected $url;

    /** 
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected $image;
}