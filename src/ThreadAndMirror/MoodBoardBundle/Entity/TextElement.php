<?php
namespace ThreadAndMirror\MoodBoardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/** 
 */
class TextElement extends Element
{

	/** 
	 * @ORM\Column(type="string")
	 */
	protected $text = 0;

	/** 
	 * @ORM\Column(type="string")
	 */
	protected $style = 0;
}