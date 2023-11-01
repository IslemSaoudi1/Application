<?php

namespace App\Form;
use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType; // Add this line
use Symfony\Bridge\Doctrine\Form\Type\EntityType; // Add this line
use App\Entity\User;
class ProfileType  extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
            ->add('jobDescription')
            ->add('Nationality')
            ->add('passport', FileType::class, [
                'label' => 'Passeport',
                'required' => false, // Remove this if it's a required field
                'data_class' => null, // Allow overwriting the file
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