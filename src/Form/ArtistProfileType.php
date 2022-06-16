<?php

namespace App\Form;

use App\Entity\Artist;
use App\Entity\MusicalStyle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtistProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('artistName', TextType::class, [
                'label' => 'Nom d\'artiste',
                'attr' => ['placeholder' => 'aoyos'],
                ])
            ->add('equipment', TextType::class, [
                'label' => 'Matériel',
                'attr' => ['placeholder' => 'Platines, contrôleur...'],
                ])
            ->add('message', TextType::class, [
                'label' => 'Message',
                'attr' => ['placeholder' => 'Je suis un super DJ mais..!'],
                ])
            ->add('musicalStyles', EntityType::class, [
                'class' => MusicalStyle::class,
                'label' => 'Genre musical',
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artist::class,
            'validation_groups' => ['djProfile'],
        ]);
    }
}