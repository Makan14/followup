<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Contact;
use App\Enum\StatutContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire de création et modification d'un contact.
 */
class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'empty_data' => '',
                'attr' => ['autocomplete' => 'name', 'maxlength' => 120],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'empty_data' => '',
                'attr' => ['autocomplete' => 'email', 'maxlength' => 180],
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['autocomplete' => 'tel', 'maxlength' => 30],
            ])
            ->add('entreprise', TextType::class, [
                'label' => 'Entreprise',
                'required' => false,
                'attr' => ['maxlength' => 120],
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => StatutContact::cases(),
                'choice_label' => static fn (StatutContact $statut): string => $statut->label(),
                'choice_value' => static fn (?StatutContact $statut): string => $statut?->value ?? '',
            ])
            ->add('dateProchaineRelance', DateType::class, [
                'label' => 'Date de prochaine relance',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'format' => 'yyyy-MM-dd',
                'html5' => true,
                'attr' => ['placeholder' => 'AAAA-MM-JJ'],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => ['rows' => 5, 'maxlength' => 5000],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'csrf_protection' => true,
            'csrf_token_id' => 'contact',
        ]);
    }
}
