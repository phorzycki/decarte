<?php
namespace AppBundle\Form;

use AppBundle\Form\Type\StringImageFileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductCollectionForm extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['images', 'default_image', 'deletion_queue', 'slugify']);
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('productType', EntityType::class, [
                'class' => 'AppBundle:ProductType',
                'label' => 'Typ',
            ])
            ->add('name', TextType::class, ['label' => 'Nazwa'])
            ->add('slugName', HiddenType::class)
            ->add('isVisible', CheckboxType::class, ['label' => 'Kolekcja widoczna na stronie', 'required' => false])
            ->add('shortDescription', TextareaType::class, ['label' => 'Opis na stronie głównej', 'required' => false, 'attr' => ['rows' => 4]])
            ->add('description', TextareaType::class, ['label' => 'Pełny opis', 'required' => false, 'attr' => ['rows' => 4]])
            ->add('imageName', StringImageFileType::class, [
                'label' => 'Miniaturka',
                'required' => false,
                'images' => $options['images'],
                'default_image' => $options['default_image'],
            ])
            ->add('save', SubmitType::class, ['label' => 'Zapisz kolekcję'])
            ->addEventSubscriber(new ProductCollectionFormListener($options));
    }
}
