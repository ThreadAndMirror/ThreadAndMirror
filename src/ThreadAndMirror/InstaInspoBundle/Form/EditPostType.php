<?php

namespace ThreadAndMirror\InstaInspoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditPostType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('products', new ProductCollectionType(), [
            'label_render' => false
        ]);

        $builder->add('save', 'button', [
            'label' => 'Save Changes',
            'attr'  => [
                'class' => 'btn btn-susccess'
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => 'ThreadAndMirror\InstaInspoBundle\Entity\Post',
            'validation_groups' => ['create']
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
