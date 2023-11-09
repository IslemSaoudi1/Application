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

            ->add('passportdeliveredOn')
            ->add('User', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'lastname', // Remplacez par le champ approprié de l'entité User
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}