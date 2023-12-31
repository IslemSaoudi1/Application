<?php

namespace App\Form;
use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType; // Add this line
use Symfony\Bridge\Doctrine\Form\Type\EntityType; // Add this line
use App\Entity\User;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\DateType;


class ProfileType  extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('jobDescription')
            ->add('Nationality')
//            ->add('passport', null , array("attr"=> array(), 'required' => false))
            ->add('passport', FileType::class, [
                'label' => 'Passeport',
                'required' => false,
                'data_class' => null,
            ])

            ->add('passportdeliveredOn',DateType::class, [
                'attr' => ['class' => 'js-datepicker'],
            ])

            ->add('User', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'lastname',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}