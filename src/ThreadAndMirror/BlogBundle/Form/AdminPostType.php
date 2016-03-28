<?php

namespace ThreadAndMirror\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use	Symfony\Component\Form\FormBuilderInterface;
use	Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdminPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->add('title', null, array(
			'label'  			=> 'Title',
			'error_bubbling' 	=> true,
		));

		$builder->add('subTitle', null, array(
			'label'     		=> 'Subtitle',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));

		$builder->add('slug', null, array(
			'label'     		=> 'Url',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));

		$builder->add('excerpt', 'textarea', array(
			'label'     		=> 'Excerpt',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));

	    $builder->add('category', null, array(
		    'label'     		=> 'Category',
		    'error_bubbling' 	=> true,
	    ));

		$builder->add('content', null, array(
			'label'     		=> 'Content',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));	

		$builder->add('image', 'text', array(
			'label'     		=> 'Feature Image',
			'error_bubbling' 	=> true,
			'attr'				=> array('class' => 'invisible'),
		));	

		$builder->add('metaTitle', null, array(
			'label'  			=> 'Meta Title',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));

		$builder->add('metaKeywords', null, array(
			'label'  			=> 'Meta Keywords',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));

		$builder->add('metaDescription', 'textarea', array(
			'label'  			=> 'Meta Description',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));

		$builder->add('height', 'hidden', array(
			'required'			=> false,
			'error_bubbling' 	=> true,
			'attr'				=> array('class' => 'layout-height')
		));
	}

	public function getName()
	{
		return 'admin_post_type';
	}
}
