<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\PostAudience;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostAudienceFormType extends AbstractType
{
    public function __construct(private readonly Security $security)
    {}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('users', EntityType::class, [
                'class' => User::class,
                'multiple' => true,
                'placeholder' => 'Choose a user',
                'query_builder' => function (EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('u')
                        ->orderBy('u.username', 'ASC')
                        ->where('u.username != :username')
                        ->setParameter('username', $this->security->getUser()->getUsername())
                        ;
                },
                'choice_label' => function (?User $user): string
                {
                    return $user ? '<img src= "/images/'. strtolower($user->getGender()) .'_icon.jpg" /> <span>'. $user->getUsername() .'</span>': '';
                },
                'autocomplete' => true,
                'options_as_html' => true,
                'data' => $options['users'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PostAudience::class,
            'users' => []
        ]);
    }
}
