<?php

namespace ThreadAndMirror\ProductsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SectionProductGalleryProductType extends AbstractType
{
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
		));

		$builder->add('url', 'text', array(
			'label'     		=> 'Url',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));

		$builder->add('image', 'text', array(
			'label'     		=> 'Fullsize Image',
			'required'			=> true,
			'error_bubbling' 	=> true,
			'attr' 				=> array('class' => 'popup-image-toggle-src'),
		));

		$builder->add('thumbnail', 'text', array(
			'label'     		=> 'Thumbnail Image',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));

		$builder->add('pid', 'text', array(
			'label'     		=> 'Internal Product ID',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));
	}

	public function getName()
	{
		return 'section_productgalleryproduct_type';
	}
}
