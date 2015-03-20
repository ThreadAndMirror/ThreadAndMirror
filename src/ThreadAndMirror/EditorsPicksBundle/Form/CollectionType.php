<?php

namespace ThreadAndMirror\EditorsPicksBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$builder->add('header', null, array(
			'label'     		=> 'Title',
			'error_bubbling' 	=> true,
		));	

		$builder->add('caption', null, array(
			'label'     		=> 'Caption',
			'error_bubbling' 	=> true,
		));	

		$builder->add('image', 'hidden', array(
			'label'     		=> 'Feature Image (Optional)',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));	

		$builder->add('layout', 'choice', array(
			'label'     		=> 'Layout',
			'error_bubbling' 	=> true,
			'expanded'			=> true,
			'choices'			=> array('outfits' => 'Outfits', 'grid' => 'Grid', 'radial' => 'Radial'),
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

		$builder->add('picks', 'collection', array(
			'type' 				=> new PickType,
			'by_reference' 		=> false,
			'allow_add'    		=> true,
			'allow_delete' 		=> true,
		));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
        	'validation_groups' => array('full'),
            'data_class' 		=> 'ThreadAndMirror\EditorsPicksBundle\Entity\Collection',
        ));
    }

	public function getName()
	{
		return 'collection_type';
	}
}
