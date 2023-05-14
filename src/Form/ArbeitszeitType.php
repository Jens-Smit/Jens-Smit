<?php

namespace App\Form;

use App\Entity\Arbeitszeit;
use App\Entity\Fehlzeiten;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArbeitszeitType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $formId = $options['form_id'];
        $builder
        ->add('form_id', HiddenType::class, [
            'mapped' => false,
            'data' => $options['form_id'], // Hier übergeben Sie die ID als Option
        ])
            ->add('datum', DateTimeType::class, [
                'attr' => ['style' => 'display:none;'],
                'label' => false,
            ])
            
            ->add('Eintrittszeit')
            ->add('Austrittszeit')
            ->add('Fehlzeit', EntityType::class,[
                'class' => Fehlzeiten::class,
                'attr' => ['style' => 'display:none;'],
                'label' => false,
            ])
            ->add('user', EntityType::class,[
                'class' => User::class,
                'attr' => ['style' => 'display:none;'],
                'label' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'class' => 'btn btn-info',
                    'name' => 'my_form_id',
                    'value' => 'save', // Hier kannst du eine variable ID einsetzen
                ],
            ])
            ->add('delate', SubmitType::class, [
                'label' => 'delate',
                'attr' => [
                    'class' => 'btn btn-info',
                    'name' => 'my_form_id_delate',
                    'value' => 'delate', // Hier kannst du eine variable ID einsetzen
                ],
            ])
            ->add('newsave', SubmitType::class, [
                'label' => 'newsave',
                'attr' => [
                    'style' => 'display:none;',
                    'class' => 'btn btn-info',
                    'name' => 'my_form_id_delate',
                    'value' => 'newsave', // Hier kannst du eine variable ID einsetzen
                ],
            ])
        ;
        $builder->setAttribute('form_id', $formId);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Arbeitszeit::class,
            'form_id' => null,
        ]);
    }
}
