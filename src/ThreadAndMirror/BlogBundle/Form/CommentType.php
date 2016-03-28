<?php

namespace ThreadAndMirror\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommentType extends AbstractType
{
	protected $login;

	function __construct($login) 
	{
		$this->login = $login;
	}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	if (!$this->login) {
    		$builder->add('author', null, array(
				'label'     		=> 'Your Name',
				'required'			=> true,
				'error_bubbling' 	=> true,
			));

			$builder->add('email', null, array(
				'label'     		=> 'Email Address',
				'required'			=> true,
				'error_bubbling' 	=> true,
				'mapped'			=> false,
			));		
    	}

		$builder->add('content', null, array(
			'label'     		=> 'Your Comment',
			'required'			=> true,
			'error_bubbling' 	=> true,
		));	
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
	    $resolver->setDefaults(array(
	        'data_class' => 'ThreadAndMirror\BlogBundle\Entity\Comment',
	    ));
	}

	public function getName()
	{
		return 'blog_comment_type';
	}
}
