<?php

namespace ThreadAndMirror\MoodBoardBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MoodBoardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$builder->add('title', null, array(
			'label'     		=> 'Title',
			'error_bubbling' 	=> true,
		));	

		$builder->add('caption', null, array(
			'label'     		=> 'Caption',
			'error_bubbling' 	=> true,
		));	

		$builder->add('background', 'hidden', array(
			'label'     		=> 'Feature Image (Optional)',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));	

		// $builder->add('elements', 'collection', array(
		// 	'type' 				=> new PickType,
		// 	'by_reference' 		=> false,
		// 	'allow_add'    		=> true,
		// 	'allow_delete' 		=> true,
		// ));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' 		=> 'ThreadAndMirror\MoodBoardBundle\Entity\MoodBoard',
        ));
    }

	public function getName()
	{
		return 'moodboard_type';
	}
}
