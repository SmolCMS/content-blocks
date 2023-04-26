<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Form\Type;

use SmolCms\Bundle\ContentBlock\Form\Transformer\ContentBlockTransformer;
use SmolCms\Bundle\ContentBlock\Mapper\InvalidMappingStrategy;
use SmolCms\Bundle\ContentBlock\Mapper\MapperInterface;
use SmolCms\Bundle\ContentBlock\Metadata\BlockMetadata;
use SmolCms\Bundle\ContentBlock\Metadata\MetadataReaderException;
use SmolCms\Bundle\ContentBlock\Metadata\MetadataRegistry;
use SmolCms\Bundle\ContentBlock\ResolvedProperty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentBlockWrapperType extends AbstractType
{
    public function __construct(
        private readonly MetadataRegistry $registry,
        private readonly MapperInterface $mapper,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'allowed' => [],
            'allow_change' => false,
            'error_bubbling' => false,
            'block_selector_options' => [],
        ]);

        $resolver->define('invalid_mapping_strategy')
            ->default(null)
            ->normalize(function (Options $options, ?InvalidMappingStrategy $value) {
                return $value ?? ($options['allow_change'] ? InvalidMappingStrategy::ERROR : InvalidMappingStrategy::THROW);
            })
            ->allowedTypes('null', InvalidMappingStrategy::class);

        $resolver->setDefault('property', null);
        $resolver->setAllowedTypes('property', [ResolvedProperty::class, 'null']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ResolvedProperty|null $property */
        $property = $options['property'];
        $allowChange = $options['allow_change'];
        $invalidMappingStrategy = $options['invalid_mapping_strategy'];
        $allowedBlocks = $this->getAllowedBlocks($options['allowed']);
        $singleChoice = count($allowedBlocks) === 1 ? array_values($allowedBlocks)[0] : null;

        if ($singleChoice) {
            $builder->add('name', HiddenType::class, [
                'data' => $singleChoice->name,
            ]);
            $this->addDataField($builder, $singleChoice, $property, $invalidMappingStrategy);
        } else {
            $isRequired = $property?->isRequired() ?? true;
            $builder->add('name', ChoiceType::class, $options['block_selector_options'] + [
                'label' => false,
                'choices' => $allowedBlocks,
                'choice_label' => fn (BlockMetadata $metadata) => $metadata->type->label ?? $metadata->label ?? $metadata->name,
                'choice_value' => 'name',
                'required' => $isRequired,
                'placeholder' => '',
            ]);

            if (!$allowChange && $property) {
                $builder->addEventListener(
                    FormEvents::PRE_SUBMIT,
                    function (FormEvent $event) use ($property) {
                        $initialData = $event->getForm()->getData()['name'];
                        if ($initialData instanceof BlockMetadata) {
                            $initialData = $initialData->name;
                        }
                        $submittedData = $event->getData()['name'] ?? null;

                        if (!$initialData || !$submittedData) {
                            return;
                        }

                        if ($submittedData !== $initialData) {
                            if ($property) {
                                throw new \UnexpectedValueException(
                                    sprintf(
                                        'Type of property "%s::$%s" is not allowed to change. Try set option `allow_change` to `true`.',
                                        $property->getClass(),
                                        $property->getPropertyName(),
                                    )
                                );
                            }

                            throw new \UnexpectedValueException(
                                'Type of property is not allowed to change. Try set option `allow_change` to `true`.'
                            );
                        }
                    }
                );
            }

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($singleChoice, $property, $invalidMappingStrategy) {
                $metadata = $singleChoice;
                $data = null;

                if (!$metadata) {
                    $data = $event->getData();
                    $metadata = $data ? $this->registry->metadataFor($data) : null;
                }

                $this->addDataField($event->getForm(), $metadata, $property, $invalidMappingStrategy);
                $event->setData([
                    'name' => $metadata,
                    'data' => $data,
                ]);
            });

            $builder->get('name')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($singleChoice, $property, $invalidMappingStrategy) {
                $metadata = $singleChoice ?? $event->getForm()->getData();

                $this->addDataField($event->getForm()->getParent(), $metadata, $property, $invalidMappingStrategy);
            });
        }

        $builder->addModelTransformer(new ContentBlockTransformer($property, $this->registry, $singleChoice, $this->mapper));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['allow_change'] = $options['allow_change'];
    }

    public function getBlockPrefix(): string
    {
        return 'smol_content_block_wrapper';
    }

    private function addDataField(FormInterface|FormBuilderInterface $form, ?BlockMetadata $blockMetadata, ?ResolvedProperty $property, InvalidMappingStrategy $invalidMappingStrategy): void
    {
        if (!$blockMetadata) {
            return;
        }

        $form->add('data', ContentBlockDataType::class, [
            'property' => $property,
            'block_metadata' => $blockMetadata,
            'type' => $blockMetadata->type,
            'invalid_mapping_strategy' => $invalidMappingStrategy,
        ]);
    }

    /**
     * @param array<array-key, string|BlockMetadata> $allowedBlocks
     * @return array<string, BlockMetadata>
     * @throws MetadataReaderException
     */
    private function getAllowedBlocks(array $allowedBlocks): array
    {
        if ($allowedBlocks) {
            return $this->registry->normalizeMetadata($allowedBlocks);
        }

        return iterator_to_array($this->registry->all());
    }
}
