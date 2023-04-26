<?php

namespace SmolCms\Bundle\ContentBlock\EventListener;

use SmolCms\Bundle\ContentBlock\Form\Type\ContentBlockWrapperType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Validator\ViolationMapper\RelativePath;
use Symfony\Component\Form\Extension\Validator\ViolationMapper\ViolationPath;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyPathBuilder;

class FormErrorMapperListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SUBMIT => 'handle',
        ];
    }

    public function handle(PostSubmitEvent $event): void
    {
        $form = $event->getForm();

        if (!$form->isRoot()) {
            return;
        }

        $this->iterateForm($form);
    }

    private function iterateForm(FormInterface $form): void
    {
        foreach ($form as $child) {
            if (!$child->getConfig()->getType()->getInnerType() instanceof ContentBlockWrapperType) {
                $this->iterateForm($child);
                continue;
            }

            $errors = $child->getErrors();
            if (count($errors) === 0) {
                continue;
            }

            if (!$child->has('data')) {
                continue;
            }

            $properties = $child->get('data');
            $clearErrors = false;

            foreach ($errors as $error) {
                $violation = $error->getCause();
                $violationPath = new ViolationPath($violation->getPropertyPath());
                $relativePath = $this->reconstructPath($violationPath, $form);
                if (!$relativePath) {
                    continue;
                }

                $elements = $relativePath->getElements();

                do {
                    $basePath = array_shift($elements);
                } while ($basePath !== $child->getName());

                if (count($elements) === 0) {
                    continue;
                }

                if ($elements[0] === 'data') {
                    continue;
                }

                $origin = $properties;

                foreach ($elements as $item) {
                    $realOrigin = $this->findRealOrigin($origin);
                    if (!$realOrigin->has($item)) {
                        continue 2;
                    }

                    $origin = $realOrigin->get($item);
                }

                $origin->addError($error);
                $clearErrors = true;
            }

            if ($clearErrors) {
                $child->clearErrors();
            }
        }
    }

    /**
     * Reconstructs a property path from a violation path and a form tree.
     */
    private function reconstructPath(ViolationPath $violationPath, FormInterface $origin): ?RelativePath
    {
        $propertyPathBuilder = new PropertyPathBuilder($violationPath);
        $it = $violationPath->getIterator();
        $scope = $origin;

        // Remember the current index in the builder
        $i = 0;

        // Expand elements that map to a form (like "children[address]")
        for ($it->rewind(); $it->valid() && $it->mapsForm(); $it->next()) {
            if (!$scope->has($it->current())) {
                // Scope relates to a form that does not exist
                // Bail out
                break;
            }

            // Process child form
            $scope = $scope->get($it->current());

            if ($scope->getConfig()->getInheritData()) {
                // Form inherits its parent data
                // Cut the piece out of the property path and proceed
                $propertyPathBuilder->remove($i);
            } else {
                /* @var \Symfony\Component\PropertyAccess\PropertyPathInterface $propertyPath */
                $propertyPath = $scope->getPropertyPath();

                if (null === $propertyPath) {
                    // Property path of a mapped form is null
                    // Should not happen, bail out
                    break;
                }

                $propertyPathBuilder->replace($i, 1, $propertyPath);
                $i += $propertyPath->getLength();
            }
        }

        $finalPath = $propertyPathBuilder->getPropertyPath();

        return null !== $finalPath ? new RelativePath($origin, $finalPath) : null;
    }

    private function findRealOrigin(FormInterface $form): FormInterface
    {
        $origin = $form;

        while ($origin->getConfig()->getType()->getInnerType() instanceof ContentBlockWrapperType) {
            $origin = $origin->get('data');
        }

        return $origin;
    }
}
