<?php

namespace App\Form;

use App\Entity\Dienste;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiensteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user')
            ->add('dienstplan')
            ->add('kommen')
            ->add('gehen')
            ->add('save', SubmitType::class,[
                'attr'=>['class'=>'w-100 btn btn-info']
            ])
            ->setAttributes(['id' => 'tims_form'])
        
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dienste::class,
        ]);
    }
}
