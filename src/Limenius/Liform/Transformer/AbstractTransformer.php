<?php

/*
 * This file is part of the Limenius\Liform package.
 *
 * (c) Limenius <https://github.com/Limenius/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Limenius\Liform\Transformer;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Nacho Martín <nacho@limenius.com>
 */
abstract class AbstractTransformer implements TransformerInterface
{
    protected TranslatorInterface $translator;
    protected ?FormTypeGuesserInterface $validatorGuesser = null;

    /**
     * @param TranslatorInterface           $translator
     * @param FormTypeGuesserInterface|null $validatorGuesser
     */
    public function __construct(TranslatorInterface $translator, FormTypeGuesserInterface $validatorGuesser = null)
    {
        $this->translator = $translator;
        $this->validatorGuesser = $validatorGuesser;
    }

    /**
     * @param FormInterface $form
     *
     * @return bool
     */
    public function isRequired(FormInterface $form): bool
    {
        return $form->getConfig()->getOption('required');
    }

    /**
     * @param array         $extensions
     * @param FormInterface $form
     * @param array         $schema
     *
     * @return array
     */
    protected function applyExtensions(array $extensions, FormInterface $form, array $schema): array
    {
        $newSchema = $schema;
        foreach ($extensions as $extension) {
            $newSchema = $extension->apply($form, $newSchema);
        }

        return $newSchema;
    }

    /**
     * @param FormInterface        $form
     * @param array                $schema
     * @param ExtensionInterface[] $extensions
     * @param string|null          $widget
     *
     * @return array
     */
    protected function addCommonSpecs(FormInterface $form, array $schema, array $extensions = [], ?string $widget = null): array
    {
        $schema = $this->addLabel($form, $schema);
        $schema = $this->addAttr($form, $schema);
        $schema = $this->addPattern($form, $schema);
        $schema = $this->addDisabled($form, $schema);
        $schema = $this->addDescription($form, $schema);
        $schema = $this->addWidget($form, $schema, $widget);
        $schema = $this->applyExtensions($extensions, $form, $schema);

        return $schema;
    }

    /**
     * @param FormInterface $form
     * @param array         $schema
     *
     * @return array
     */
    protected function addDisabled(FormInterface $form, array $schema): array
    {
        if ($form->getConfig()->getOption('disabled')) {
            $schema['disabled'] = true;
        }

        return $schema;
    }

    /**
     * @param FormInterface $form
     * @param array         $schema
     *
     * @return array
     */
    protected function addPattern(FormInterface $form, array $schema): array
    {
        if ($attr = $form->getConfig()->getOption('attr')) {
            if (isset($attr['pattern'])) {
                $schema['pattern'] = $attr['pattern'];
            }
        }

        return $schema;
    }

    /**
     * @param FormInterface $form
     * @param array         $schema
     *
     * @return array
     */
    protected function addLabel(FormInterface $form, array $schema): array
    {
        $translationDomain = $form->getConfig()->getOption('translation_domain');
        $label = $form->getConfig()->getOption('label');
        if (false === $label) {
            $schema['title'] = '';
        } elseif (!empty($label)) {
            $schema['title'] = $this->translator->trans($label, [], $translationDomain);
        } else {
            $schema['title'] = $this->translator->trans($form->getName(), [], $translationDomain);
        }

        return $schema;
    }

    /**
     * @param FormInterface $form
     * @param array         $schema
     *
     * @return array
     */
    protected function addAttr(FormInterface $form, array $schema): array
    {
        if ($attr = $form->getConfig()->getOption('attr')) {
            if (!empty($attr['placeholder']) && is_string($attr['placeholder'])) {
                $translationDomain = $form->getConfig()->getOption('translation_domain');
                $attr['placeholder'] = $this->translator->trans($attr['placeholder'], [], $translationDomain);
            }
            $schema['attr'] = $attr;
        }

        return $schema;
    }

    /**
     * @param FormInterface $form
     * @param array         $schema
     *
     * @return array
     */
    protected function addDescription(FormInterface $form, array $schema): array
    {
        $formConfig = $form->getConfig();
        if ($help = $formConfig->getOption('help', '')) {
            $schema['description'] = $this->translator->trans($help);
        }

        if ($liform = $formConfig->getOption('liform')) {
            if (isset($liform['description']) && $description = $liform['description']) {
                $schema['description'] = $this->translator->trans($description);
            }
        }

        return $schema;
    }

    /**
     * @param FormInterface $form
     * @param array         $schema
     * @param mixed         $configWidget
     *
     * @return array
     */
    protected function addWidget(FormInterface $form, array $schema, $configWidget): array
    {
        if ($liform = $form->getConfig()->getOption('liform')) {
            if (isset($liform['widget']) && $widget = $liform['widget']) {
                $schema['widget'] = $widget;
            }
        } elseif ($configWidget) {
            $schema['widget'] = $configWidget;
        }

        return $schema;
    }
}
