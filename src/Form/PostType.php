<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\PostRestriction;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Leapt\FroalaEditorBundle\Form\Type\FroalaEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

class PostType extends AbstractType
{

    public function __construct(private readonly Security $security)
    {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'What\'s on your mind, ' . $this->security->getUser()->getUserIdentifier() . '?',
                ],
            ])
            ->add('audienceType', ChoiceType::class, [
                'choices' => [
                    'Public' => 'public',
                    'Friends' => 'friends',
                    'Friends except...' => 'friends_except',
                    'Specific friends' => 'specific_friends',
                    'Only Me' => 'only_me',
                ],
            ])
            ->add('postAudience', PostAudienceFormType::class, [
                'required' => false,
                'users' => $options['users'],
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'placeholder' => 'Choose a Category',
                'multiple' => true,
                'min_characters' => 1,
                'autocomplete' => true,
                'options_as_html' => true,
                'query_builder' => function (EntityRepository $entityRepository): QueryBuilder {
                    return $entityRepository->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'choice_label' => function (?Category $category): string {
                    return $category ? $category->getIcon() .' <span>'. $category->getName() .'</span>' : '';
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'users' => []
        ]);
    }
}
