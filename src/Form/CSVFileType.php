<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class CSVFileType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class,
                [
                    'label' => 'CSV File',
                    'mapped' => 'false',
                    'constraints' => [
                        new File([
                            'mimeTypes' => [
                                'application/csv',
                                'text/plain'
                            ],
                            'mimeTypesMessage' => 'Please upload file with .csv extension'
                        ])
                    ]
                ]
            )
            ->add('test', CheckboxType::class, ['label' => 'Submit in test mode?', 'required' => false])
            ->add('Submit', SubmitType::class, ['label' => 'Upload file']);
    }
}

