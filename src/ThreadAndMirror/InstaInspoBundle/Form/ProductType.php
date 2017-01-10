<?php

namespace ThreadAndMirror\InstaInspoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductType extends AbstractType
{
    /**
     *{@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('designer', null, [
            'label' => 'Designer'
        ]);

        $builder->add('name', null, [
            'label' => 'Name'
        ]);

        $builder->add('store', null, [
            'label' => 'Store'
        ]);

        $builder->add('image', null, [
            'label' => 'Image'
        ]);

        $builder->add('url', null, [
            'label' => 'Url'
        ]);
    }

    /**
     *{@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'ThreadAndMirror\InstInspoBundle\Entity\Product'
        ]);
    }

    /**
     *{@inheritdoc}
     */
    public function getName()
    {
        return 'product_type';
    }
}
