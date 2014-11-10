<?php
namespace ThreadAndMirror\AlertBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;


/** 
 * @ORM\Table(name="tam_alert_archive_backinstock")
 * @ORM\Entity()
 */
class ArchiveBackInStock
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
    protected $alert;

    /** 
     * @ORM\Column(type="integer")
     */
    protected $product;

    /** 
     * @ORM\Column(type="integer")
     */
    protected $notifications;

    /** 
     * @ORM\Column(type="datetime")
     */
    protected $added;

    /** 
     * @ORM\Column(type="datetime")
     */
    protected $processed;

    /** 
     * @ORM\Column(type="datetime")
     */
    protected $archived;

    
    /**
    * Build an archived copy of an alert
    * @param $alert     AlertBackInStock instance
    */
    public function __construct($alert)
    {
        $this->alert = $alert->getId();
        $this->product = $alert->getProduct();
        $this->notifications = $alert->getNotifications();
        $this->added = $alert->getAdded();
        $this->processed = $alert->getProcessed();

        $this->archived = new \DateTime;
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
}