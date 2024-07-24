<?php

/*
 * This file is part of the Limenius\Liform package.
 *
 * (c) Limenius <https://github.com/Limenius/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Limenius\Liform;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

/**
 * @author Nacho Martín <nacho@limenius.com>
 */
class FormUtil
{
    /**
     * @param FormInterface $form
     *
     * @return array
     */
    public static function typeAncestry(FormInterface $form): array
    {
        $types = [];
        self::typeAncestryForType($form->getConfig()->getType(), $types);

        return $types;
    }

    /**
     * @param ResolvedFormTypeInterface|null $formType
     * @param array                          $types
     *
     * @return void
     */
    public static function typeAncestryForType(?ResolvedFormTypeInterface $formType, array &$types): void
    {
        if (!($formType instanceof ResolvedFormTypeInterface)) {
            return;
        }

        $types[] = $formType->getBlockPrefix();

        self::typeAncestryForType($formType->getParent(), $types);
    }

    /**
     * Returns the dataClass of the form or its parents, if any
     *
     * @param mixed $formType
     *
     * @return string|null the dataClass
     */
    public static function findDataClass(mixed $formType): ?string
    {
        if ($dataClass = $formType->getConfig()->getDataClass()) {
            return $dataClass;
        } else {
            if ($parent = $formType->getParent()) {
                return self::findDataClass($parent);
            } else {
                return null;
            }
        }
    }

    /**
     * @param FormInterface $form
     * @param mixed         $type
     *
     * @return bool
     */
    public static function isTypeInAncestry(FormInterface $form, mixed $type): bool
    {
        return in_array($type, self::typeAncestry($form));
    }

    /**
     * @param FormInterface $form
     *
     * @return string
     */
    public static function type(FormInterface $form): string
    {
        return $form->getConfig()->getType()->getBlockPrefix();
    }

    /**
     * @param FormInterface $form
     *
     * @return string|null
     */
    public static function label(FormInterface $form): ?string
    {
        return $form->getConfig()->getOption('label', $form->getName());
    }

    /**
     * @param FormInterface $form
     *
     * @return bool
     */
    public static function isCompound(FormInterface $form): bool
    {
        return $form->getConfig()->getOption('compound');
    }
}
