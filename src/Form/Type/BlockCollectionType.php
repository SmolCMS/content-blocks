<?php

/*
 * @author       Kamil Smolak <kamil@smol.pl>
 * @link         http://www.smol.pl
 * @copyright    Copyright (c) 2022 Kamil Smolak
 */

namespace SmolCms\Bundle\ContentBlock\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

class BlockCollectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'entry_type' => BlockWrapperType::class,
            'entry_options' => [
                'block_prefix' => 'smol_block_collection_entry',
            ],
            'allow_add' => true,
            'allow_delete' => true,
            'allow_move' => true,
            'block_prefix' => 'smol_block_collection',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['allow_move']) {
            $prototype = $builder->create('move_up', ButtonType::class);
            $builder->setAttribute('move_up_prototype', $prototype->getForm());

            $prototype = $builder->create('move_down', ButtonType::class);
            $builder->setAttribute('move_down_prototype', $prototype->getForm());
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $this->finishMovePrototypes($form, $view);
    }

    public function getParent(): string
    {
        return LiveCollectionType::class;
    }

    private function finishMovePrototypes(FormInterface $form, FormView $view): void
    {
        if (
            $form->getConfig()->hasAttribute('move_up_prototype')
            && $form->getConfig()->hasAttribute('move_down_prototype')
        ) {
            $prototypeMoveUp = $form->getConfig()->getAttribute('move_up_prototype');
            $prototypeMoveDown = $form->getConfig()->getAttribute('move_down_prototype');
            $index = 0;
            $countEntries = count($form);

            if ($countEntries < 2) {
                return;
            }

            foreach ($form as $k => $entry) {
                $entryView = $view[$k];

                $buttonMoveUp = clone $prototypeMoveUp;
                $buttonMoveUp->setParent($entry);
                $buttonMoveUpView = $buttonMoveUp->createView($entryView);
                $entryView->vars['button_move_up'] = $this->finishViewMoveButton(
                    $buttonMoveUpView,
                    $view->vars['full_name'],
                    $k,
                    'up',
                    $index > 0
                );

                $buttonMoveDown = clone $prototypeMoveDown;
                $buttonMoveDown->setParent($entry);
                $buttonMoveDownView = $buttonMoveDown->createView($entryView);
                $entryView->vars['button_move_down'] = $this->finishViewMoveButton(
                    $buttonMoveDownView,
                    $view->vars['full_name'],
                    $k,
                    'down',
                    $index < ($countEntries - 1)
                );

                $index++;
            }
        }
    }

    private function finishViewMoveButton(
        FormView $buttonView,
        string $collectionName,
        mixed $key,
        string $direction,
        bool $enabled
    ): FormView {
        $attr = $buttonView->vars['attr'];
        $attr['data-action'] ??= 'live#action';
        $attr['data-action-name'] ??= sprintf(
            'moveCollectionItem(name=%s, index=%s, move=%s)',
            $collectionName,
            $key,
            $direction,
        );
        $buttonView->vars['attr'] = $attr;
        $buttonView->vars['disabled'] = !$enabled;

        array_splice($buttonView->vars['block_prefixes'], 1, 0, 'live_collection_button_move_' . $direction);

        return $buttonView;
    }
}
