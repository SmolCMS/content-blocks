<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Form\Type;

use SmolCms\Bundle\ContentBlock\Form\Transformer\ContentBlockTransformer;
use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\Metadata\ContentBlockRegistry;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockWrapperType extends AbstractType
{
    public function __construct(
        private readonly ContentBlockRegistry $registry,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'allowed' => [],
            'allow_change' => false,
        ]);

        $resolver->setDefault('property', null);
        $resolver->setAllowedTypes('property', [ResolvedProperty::class, 'null']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $property = $options['property'];
        $choices = $this->getMetadataChoices($options['allowed']);
        $singleChoice = count($choices) === 1 ? array_values($choices)[0] : null;

        if ($singleChoice) {
            $builder->add('name', HiddenType::class, [
                'data' => $singleChoice->name,
            ]);
            $this->addBlockField($builder, $singleChoice, $property);
        } else {
            $builder->add('name', ChoiceType::class, [
                'label' => false,
                'choices' => $choices,
                'choice_label' => fn (BlockMetadata $metadata) => $metadata->type->label ?? $metadata->label ?? $metadata->name,
                'choice_value' => 'name',
                'placeholder' => '',
            ]);

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($singleChoice, $property) {
                $metadata = $singleChoice ?? $event->getData()['name'] ?? null;

                $this->addBlockField($event->getForm(), $metadata, $property);
            });

            $builder->get('name')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($singleChoice, $property) {
                $metadata = $singleChoice ?? $event->getForm()->getData();

                $this->addBlockField($event->getForm()->getParent(), $metadata, $property);
            });
        }

        $builder->addModelTransformer(new ContentBlockTransformer($this->registry, $singleChoice));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['allow_change'] = $options['allow_change'];
    }

    public function addBlockField(FormInterface|FormBuilderInterface $form, ?BlockMetadata $metadata, ?ResolvedProperty $property): void
    {
        if (!$metadata) {
            return;
        }

        $form->add('properties', BlockType::class, [
            'property' => $property,
            'block_metadata' => $metadata,
            'type' => $metadata->type,
        ]);
    }

    private function getMetadataChoices(array $allowed): array
    {
        //todo
        if ($allowed) {
            $items = [];

            foreach ($allowed as $name) {
                $items[] = is_string($name) ? $this->registry->metadataFor($name) : $name;
            }

            return $items;
        }

        return iterator_to_array($this->registry->all());
    }
}
