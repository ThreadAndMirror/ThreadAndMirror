<?php
namespace ThreadAndMirror\ProductsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/** 
 * @ORM\Table(name="tam_offer")
 * @ORM\Entity()
 */
class Offer
{
	/** 
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Shop", inversedBy="offers")
	 * @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
	 */
	protected $shop;

	/** 
	 * @ORM\Column(type="string", length=32, nullable=true)
	 */
	protected $code = null;

	/** 
	 * @ORM\Column(type="text")
	 */
	protected $description;

	/** 
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $url;

	/** 
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $start;

	/** 
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $end;

	/** 
	 * @ORM\Column(type="boolean")
	 */
	protected $hidden;

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
     * Set code
     *
     * @param string $code
     * @return Offer
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Offer
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Offer
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
     * Set start
     *
     * @param \DateTime $start
     * @return Offer
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     * @return Offer
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime 
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set shop
     *
     * @param \ThreadAndMirror\ProductsBundle\Entity\Shop $shop
     * @return Offer
     */
    public function setShop(\ThreadAndMirror\ProductsBundle\Entity\Shop $shop = null)
    {
        $this->shop = $shop;

        return $this;
    }

    /**
     * Get shop
     *
     * @return \ThreadAndMirror\ProductsBundle\Entity\Shop 
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * Set hidden
     *
     * @param boolean $hidden
     * @return Offer
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Get hidden
     *
     * @return boolean 
     */
    public function getHidden()
    {
        return $this->hidden;
    }
}
