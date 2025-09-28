<?php

namespace App\Form;

use App\Entity\Quiz;
use App\Entity\Tentative;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TentativeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('quiz_id', HiddenType::class, [
                'mapped' => false,
                'data' => $options['quiz_id'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tentative::class,
            'quiz_id' => null,
        ]);
        $resolver->setAllowedTypes('quiz_id', ['null','int','string']);
    }
}
