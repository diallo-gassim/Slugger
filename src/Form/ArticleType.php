<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('slug', TextType::class, [ 
                'label' => 'Nom de l\'url',
                'constraints' => [
                    // Ajoutez une contrainte Regex pour vérifier que le slug ne contient que des lettres minuscules et des tirets.
                    new Regex([
                        'pattern' => '/^[a-z]+(?:-[a-z]+)*$/',
                        'message' => 'Le slug ne doit contenir que des lettres minuscules et des tirets.',
                    ]),
                ],
            ])
            ->add('content')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
