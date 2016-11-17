<?php

namespace ThreadAndMirror\InstaInspoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class CreatePostType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('url', null, [
            'label'       => 'Instagram Post Url',
            'required'    => true,
            'constraints' => [
                new Url(),
                new NotBlank()
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'ThreadAndMirror\InstaInspoBundle\Entity\Post',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'instainspo_create_post_type';
    }
}
