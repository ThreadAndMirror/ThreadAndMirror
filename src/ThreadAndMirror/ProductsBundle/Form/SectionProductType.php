<?php

namespace ThreadAndMirror\ProductsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SectionProductType extends AbstractType
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

		$builder->add('pid', 'hidden', array(
			'error_bubbling' 	=> true,
			'required'			=> false,
		));

	    $builder->add('position', 'hidden', array(
		    'error_bubbling' 	=> true,
		    'required'			=> false,
	    ));

	    $builder->add('effect', 'hidden', array(
		    'error_bubbling' 	=> true,
		    'required'			=> false,
	    ));
    }

	public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ThreadAndMirror\ProductsBundle\Entity\SectionProduct',
        ));
    }

	public function getName()
	{
		return 'section_product_type';
	}
}
