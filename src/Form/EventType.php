<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Task;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('duration', NumberType::class, [
                'label' => 'Durée (en heures)'
            ])
            ->add('date', DateType::class, [
               'widget' => 'single_text',
               'label' => 'Le',
            ])
            ->add('task', EntityType::class, [
                'class' => Task::class,
                'choice_label' => function ($task) {
                    return $task->getCategory()->getName() . ' - ' . $task->getName();
                },
                'label' => 'Tâche associée'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
