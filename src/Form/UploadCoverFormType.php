<?php

namespace App\Form;

use App\Entity\PostAudience;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\UX\Dropzone\Form\DropzoneType;

class UploadCoverFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('coverPhoto', DropzoneType::class, [
                "attr" => [
                    "placeholder" => "Drag and drop or browse your image"
                ],
                "mapped" => false,
                "constraints" => [
                    new Image([
                        "maxSize" => "2048k",
                        "detectCorrupted" => true,
                        "maxRatio" => 3.0,
                        "mimeTypesMessage" => "Please upload a valid image"
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
