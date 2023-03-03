<?php

namespace App\Form;

use App\Entity\Show;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class ShowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => "Nom de l'événement"
            ])
            ->add('description', null, [
                'label' => "Description"
            ])
            ->add('date_start', null, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => ['step' => '1']
            ])
            ->add('date_end', null, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => ['step' => '1']
            ])
            ->add('showPosterImageFile', VichFileType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => true,
                'label' => 'Show Poster Image',
            ])
            ->add('categories', null, [
                'label' => 'Catégories'
            ])
            ->add('ok', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Show::class,
        ]);
    }
}
