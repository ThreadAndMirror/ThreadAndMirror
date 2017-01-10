<?php

namespace ThreadAndMirror\InstaInspoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductCollectionType extends AbstractType
{
    /**
     *{@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('products', 'collection', [
            'type'           => new ProductType(),
            'label_render'   => false,
            'required'       => false,
            'error_bubbling' => true,
        ]);

        $builder->add('link', 'button', [
            'label' => 'Add Product From Link',
            'attr'  => [
                'class' => 'btn btn-primary'
            ]
        ]);

        $builder->add('add', 'button', [
            'label' => 'Add Product Manually',
            'attr'  => [
                'class' => 'btn btn-primary'
            ]
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'product_collection_type';
    }
}
