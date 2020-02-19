<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditProductType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('productCode', TextType::class, ['label' => 'Product Code'])
            ->add('productName', TextType::class, ['label' => 'Product Name'])
            ->add('productDescription', TextType::class, ['label' => 'Product Description'])
            ->add('stock', TextType::class, ['label' => 'Stock'])
            ->add('cost', TextType::class, ['label' => 'Cost in GBP'])
            ->add('discontinued', TextType::class,
                [
                    'label' => 'Discontinued',
                    'required' => false
                ])
            ->add('category', TextType::class, ['label' => 'Category'])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
