<?php
namespace ThreadAndMirror\AlertBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;


/** 
 * @ORM\Table(name="tam_alert_archive_furtherpricechange")
 * @ORM\Entity()
 */
class ArchiveFurtherPriceChange
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
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected $was = 0.00;

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

	/**
    * Build an archived copy of an alert
    * @param $alert     AlertFurtherPriceChange instance
    */
    public function __construct($alert)
    {
        $this->alert = $alert->getId();
        $this->product = $alert->getProduct();
        $this->was = $alert->getWas();
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