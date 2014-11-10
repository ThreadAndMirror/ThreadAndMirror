<?php

namespace ThreadAndMirror\ProductsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SectionProductGalleryType extends AbstractType
{
	protected $id;

	public function __construct($link)
	{
		$this->id = $link->getId();
	}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->add('heading', 'text', array(
			'label'     		=> 'Heading',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));	

		$builder->add('caption', 'textarea', array(
			'label'     		=> 'Caption',
			'required'			=> false,
			'error_bubbling' 	=> true,
			'attr'				=> array('class' => 'markitup'),
		));	

		$builder->add('style', 'choice', array(
			'label'     		=> 'Layout',
			'error_bubbling' 	=> true,
			'expanded'			=> true,
			'choices'			=> array('carousel' => 'Carousel', 'feature' => 'Feature', 'radial' => 'Radial', 'collection' => 'Collection'),
		));	
	}

	public function getName()
	{
		return $this->id.'_section_productgallery_type';
	}
}
