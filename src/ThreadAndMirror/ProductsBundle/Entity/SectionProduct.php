<?php

namespace ThreadAndMirror\ProductsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use ThreadAndMirror\ProductsBundle\Entity\Product;
use Gedmo\Mapping\Annotation as Gedmo;

/** 
 * @ORM\Table(name="tam_products_section_product")
 * @ORM\Entity()
 */
class SectionProduct
{
    /** 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $designer;

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /** 
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $pid;

    /** 
     * @ORM\Column(type="string", nullable=true, length=1024)
     */
    protected $url;

    /** 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $image;

	/**
	 * @ORM\Column(type="text")
	 */
	protected $position = 'squared';

	/**
	 * @ORM\Column(type="text")
	 */
	protected $effect = 'cutout';

    public function __construct($product = null)
    {
        if ($product instanceof Product) {
            $this->designer    = $product->getBrand() !== null ? $product->getBrand()->getName() : null;
            $this->name        = $product->getName();
            $this->description = $product->getShop() !== null ? 'At '.$product->getShop()->getName() : '';
            $this->url         = $product->getFrontendUrl();
            $this->image       = $product->getImage();
            $this->pid         = $product->getId();
        }
    }

	/**
	 * Build the html for rendering in the front end, using any nessary custom code
	 *
	 * @param  Sections 	$services  	The section manager service
	 * @param  Section 		$link  		The section link entity
	 * @return string 					The rendered section html
	 */
	public function render($services, $link)
	{
		// Render the twig template
		return $services->getTwig()->render('ThreadAndMirrorProductsBundle:Section:product.html.twig', array(
			'section'   => $this,
			'link'      => $link,
		));
	}

	/**
	 * Build the html for admin editor form
	 *
	 * @param  Sections 	$services  	The section manager service
	 * @param  Section 		$link  		The section link entity
	 * @return string 					The rendered html for the section admin form
	 */
	public function editor($services, $link)
	{
		// Build the section from using the generic builder method
		$form = $services->createSectionForm($link, $this);

		// Render the admin form html
		return $services->getTwig()->render('ThreadAndMirrorProductsBundle:Section:productForm.html.twig', array(
			'form'      => $form->createView(),
			'section'	=> $this,
			'link'      => $link,
		));
	}

	/**
	 * Update the section from posted data
	 *
	 * @param  Sections 	$services  		The section manager service
	 * @param  array 		$parameters 	Posted parameters for this section's form
	 * @param  Request  	$request 		The request object
	 * @param  Section 		$link  			The section link entity
	 */
	public function save($services, $parameters, $request, $link)
	{
		// Save the values
		$this->setDesigner($parameters['designer']);
		$this->setName($parameters['name']);
		$this->setDescription($parameters['description']);
		$this->setImage($parameters['image']);
		$this->setUrl($parameters['url']);
		$this->setPid($parameters['pid']);
		$this->setPosition($parameters['position']);
		$this->setEffect($parameters['effect']);

		$services->getManager()->persist($this);
	}

	/**
	 * Update the product data using a product entity
	 *
	 * @param  Product $product
	 */
	public function updateFromProduct(Product $product)
	{
		$this->designer    = $product->getBrand() !== null ? $product->getBrand()->getName() : null;
		$this->name        = $product->getName();
		$this->description = $product->getShop() !== null ? 'At '.$product->getShop()->getName() : '';
		$this->url         = $product->getFrontendUrl();
		$this->image       = $product->getImage();
		$this->pid         = $product->getId();
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
     * Set designer
     *
     * @param string $designer
     * @return SectionProduct
     */
    public function setDesigner($designer)
    {
        $this->designer = $designer;
    
        return $this;
    }

    /**
     * Get designer
     *
     * @return string 
     */
    public function getDesigner()
    {
        return $this->designer;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return SectionProduct
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
     * Set description
     *
     * @param string $description
     * @return SectionProduct
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
     * Set pid
     *
     * @param integer $pid
     * @return SectionProduct
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    
        return $this;
    }

    /**
     * Get pid
     *
     * @return integer 
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return SectionProduct
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
     * @return SectionProduct
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
	 * Set effect
	 *
	 * @param string $effect
	 * @return SectionProduct
	 */
	public function setEffect($effect)
	{
		$this->effect = $effect;

		return $this;
	}

	/**
	 * Get effect
	 *
	 * @return string
	 */
	public function getEffect()
	{
		return $this->effect;
	}

	/**
     * Set deleted
     *
     * @param boolean $deleted
     * @return SectionProduct
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
     * Set position
     *
     * @param string $position
     * @return SectionProduct
     */
    public function setPosition($position)
    {
        $this->position = $position;
    
        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

	/**
	 * Set link
	 *
	 * @param string $link
	 * @return SectionImage
	 */
	public function setLink($link)
	{
		$this->link = $link;

		return $this;
	}

	/**
	 * Get link
	 *
	 * @return string
	 */
	public function getLink()
	{
		return $this->link;
	}
}