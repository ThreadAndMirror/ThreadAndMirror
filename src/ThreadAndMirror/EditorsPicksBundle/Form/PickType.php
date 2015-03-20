<?php

namespace ThreadAndMirror\EditorsPicksBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$builder->add('designer', null, array(
			'label'     		=> 'Designer',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));	

		$builder->add('name', null, array(
			'label'     		=> 'Name',
			'error_bubbling' 	=> true,
		));	

		$builder->add('description', null, array(
			'label'     		=> 'Description Text (Optional)',
			'required'			=> false,
			'error_bubbling' 	=> true,
		));

		$builder->add('image', null, array(
			'label'     		=> 'Image',
			'error_bubbling' 	=> true,
		));	

		$builder->add('url', null, array(
			'label'     		=> 'Url',
			'error_bubbling' 	=> true,
		));	

		$builder->add('product', 'hidden', array(
			'error_bubbling' 	=> true,
		));	
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ThreadAndMirror\EditorsPicksBundle\Entity\Pick',
        ));
    }

	public function getName()
	{
		return 'pick_type';
	}
}
