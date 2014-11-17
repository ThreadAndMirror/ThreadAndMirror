<?php

namespace ThreadAndMirror\ProductsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/** 
 * @ORM\Table(name="tam_category", indexes={
 *    @ORM\Index(name="name_index", columns={"name"}),
 *    @ORM\Index(name="slug_index", columns={"slug"}),
 *    @ORM\Index(name="affiliate_window_id_index", columns={"affiliateWindowId"})
 * })
 * @ORM\Entity()
 */
class Category
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
    protected $name;

    /** 
     * @ORM\Column(type="string")
     */
    protected $slug;

    /** 
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $affiliateWindowId = null; 

    /**
     * @ORM\OneToMany(targetEntity="Product", mappedBy="category")
     * @ORM\OrderBy({"added" = "DESC"})
     */
    protected $products; 
}