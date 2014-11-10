<?php

namespace ThreadAndMirror\ProductsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SectionOutfitPickerType extends AbstractType
{
	protected $id;

	public function __construct($link)
	{
		$this->id = $link->getId();
	}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->add('title', 'textarea', array(
			'label'     		=> 'Title (Optional)',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));	

		$builder->add('content', 'textarea', array(
			'label'     		=> 'Description Text (Optional)',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));	

		$builder->add('products', 'hidden', array(
			'error_bubbling' 	=> true,
		));	
	}

	public function getName()
	{
		return $this->id.'_section_outfitpicker_type';
	}
}
