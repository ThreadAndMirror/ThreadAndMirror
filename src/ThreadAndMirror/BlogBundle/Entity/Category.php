<?php

namespace ThreadAndMirror\BlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/** 
 * @ORM\Entity()
 * @ORM\Table(name="stm_blog_category")
 */
class Category
{
	const ARTICLES = 'articles';

	const EDITORS_PICKS = 'editors-picks';

    /** 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @
     */
    protected $id;

    /** 
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected $name;

    /** 
     * @ORM\Column(type="string")
     * @Gedmo\Slug(fields={"name"}, separator="-")
     */
    protected $slug;

    /**
     * @ORM\OneToMany(targetEntity="Post", mappedBy="category")
     */
    protected $posts;

	public function __toString()
	{
		return $this->name;
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
     * @return Category
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
     * Set slug
     *
     * @param string $slug
     * @return Category
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    
        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Add post
     *
     * @param ThreadAndMirror\BlogBundle\Entity\Post $post
     */
    public function addPost(\ThreadAndMirror\BlogBundle\Entity\Post $post)
    {
        $this->posts[] = $post;
    }

    /**
     * Get posts
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getPosts()
    {
        return $this->posts;
    }
}