<?php
namespace ThreadAndMirror\AlertBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;


/** 
 * @ORM\Table(name="tam_alert_alert_sizeinstock")
 * @ORM\Entity(repositoryClass="ThreadAndMirror\AlertBundle\Repository\AlertSizeInStockRepository")
 */
class AlertSizeInStock
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
	protected $product;

	/** 
	 * @ORM\Column(type="string")
	 */
	protected $size;

	/** 
	 * @ORM\Column(type="integer")
	 */
	protected $notifications = 0;

	/** 
	 * @ORM\Column(type="datetime")
	 */
	protected $added;

	/** 
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $processed = null;

	public function __construct($product, $size)
	{
		$this->product = $product->getId();
		$this->size = $size;
		$this->added = new \DateTime;
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
	 * Set product
	 *
	 * @param integer $product
	 * @return AlertSizeInStock
	 */
	public function setProduct($product)
	{
		$this->product = $product;
	
		return $this;
	}

	/**
	 * Get product
	 *
	 * @return integer 
	 */
	public function getProduct()
	{
		return $this->product;
	}

	/**
	 * Set size
	 *
	 * @param string $size
	 * @return AlertSizeInStock
	 */
	public function setSize($size)
	{
		$this->size = $size;
	
		return $this;
	}

	/**
	 * Get size
	 *
	 * @return string 
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * Set notifications
	 *
	 * @param integer $notifications
	 * @return AlertSizeInStock
	 */
	public function setNotifications($notifications)
	{
		$this->notifications = $notifications;
	
		return $this;
	}

	/**
	 * Get notifications
	 *
	 * @return integer 
	 */
	public function getNotifications()
	{
		return $this->notifications;
	}

	/**
	 * Increment notification count
	 *
	 * @return AlertSizeInStock
	 */
	public function addNotification()
	{
		$this->notifications++;
	
		return $this;
	}

	/**
	 * Set added
	 *
	 * @param DateTime $added
	 * @return AlertSizeInStock
	 */
	public function setAdded($added)
	{
		$this->added = $added;
	
		return $this;
	}

	/**
	 * Get added
	 *
	 * @return DateTime 
	 */
	public function getAdded()
	{
		return $this->added;
	}

	/**
	 * Set processed
	 *
	 * @param DateTime $processed
	 * @return AlertSizeInStock
	 */
	public function setProcessed($processed)
	{
		$this->processed = $processed;
	
		return $this;
	}

	/**
	 * Get processed
	 *
	 * @return DateTime 
	 */
	public function getProcessed()
	{
		return $this->processed;
	}
}