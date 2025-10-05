<?php

namespace App\Form;

use App\Entity\Question;
use App\Entity\Reponse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $question = $options['question'];

        $builder
            ->add('reponse', EntityType::class, [
                'class' => Reponse::class,
                'choices' => $question->getReponses(),
                'choice_label' => fn(Reponse $reponse) => $reponse->getTexte(),
                'expanded' => true,
                'multiple' => false,
                'mapped'   => false, 
                'attr' => ['class' => 'choice-item'],
                'row_attr' => ['class' => 'choice-group']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('question');
        $resolver->setDefaults([
            'question' => null,
        ]);
        $resolver->setAllowedTypes('question', Question::class);
    }
}




