<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_element_linked_checkbox".
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TRITUM\FormElementLinkedCheckbox\Hooks;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Model\Renderable\RootRenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Service\TranslationService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Form rendering hook to resolve links in label of LinkedCheckbox elements.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class FormElementLinkResolverHook
{
    /**
     * @var string Form element type to match
     */
    private $type = 'LinkedCheckbox';

    /**
     * @var FormRuntime The current form runtime
     */
    private $formRuntime;

    /**
     * Resolve link in label of form elements with type LinkedCheckbox.
     */
    public function afterInitializeCurrentPage(FormRuntime $formRuntime, ?Page $currentPage): ?Page
    {
        $renderables = $formRuntime->getFormDefinition()->getRenderablesRecursively();

        foreach ($renderables as $renderable) {
            $this->processCharacterSubstitution($formRuntime, $renderable);
        }

        return $currentPage;
    }

    /**
     * Resolve link in label of form elements with type LinkedCheckbox.
     *
     * @param FormRuntime $formRuntime
     * @param RootRenderableInterface $renderable
     */
    private function processCharacterSubstitution(FormRuntime $formRuntime, RootRenderableInterface $renderable): void
    {
        $this->formRuntime = $formRuntime;

        // Only process linkText parsing if renderable matches given type
        if (!($renderable instanceof GenericFormElement) || $renderable->getType() !== $this->type) {
            return;
        }

        $label = $this->translate($renderable, ['label']);
        $properties = $renderable->getProperties();

        // Check if form element label contains any argument flags such as %s.
        // This also checks if one tries to use the percent sign as regular
        // character instead of a flag marked for inserting the translated
        // linkText. It needs to be set as double-percent (%%) substring.
        // If character substitution is NOT requested, enforce the link to
        // be prepended to the label text.
        if (!self::needsCharacterSubstitution($label)) {
            $label .= ' %s';
        }

        // Resolve all label arguments and merge them together in order to
        // use it for later translation of the label. The following
        // configuration methods are considered:
        // - "single configuration" via properties pageUid / linkText
        // - "array configuration" via property "additionalLinks"
        $singleLinkArgument = $this->buildArgumentFromSingleConfiguration($renderable);
        $additionalLinkArguments = $this->buildArgumentsFromArrayConfiguration($renderable);
        $labelArguments = array_merge([$singleLinkArgument], $additionalLinkArguments);

        // Provide translated link as argument for the form element label
        $renderable->setRenderingOption('translation', [
            'arguments' => [
                'label' => $labelArguments,
            ],
        ]);

        // Run translation again and override final label
        // (with translated links) as well as it will be used
        // as default value if no translation is provided
        $translatedLabel = vsprintf($label, $labelArguments);
        if (is_string($translatedLabel)) {
            $renderable->setLabel($translatedLabel);
        }

        // Reset custom properties in order to avoid additional
        // link rendering in template
        $renderable->setProperty('linkText', null);
        $renderable->setProperty('pageUid', null);
        $renderable->setProperty('additionalLinks', null);

        // Set fallback value to original property values
        // to allow other hooks making use of these ones
        $renderable->setProperty('_label', $label);
        $renderable->setProperty('_linkText', $singleLinkArgument);
        $renderable->setProperty('_pageUid', (int)$properties['pageUid']);
        $renderable->setProperty('_additionalLinks', $additionalLinkArguments);
        $renderable->setProperty('_linksProcessed', true);
    }

    /**
     * Build translation argument for label from single configuration.
     *
     * Returns the resolved argument from properties "pageUid" and "linkText"
     * (default configuration).
     *
     * @param GenericFormElement $element
     * @return string
     */
    private function buildArgumentFromSingleConfiguration(GenericFormElement $element): string
    {
        $properties = $element->getProperties();
        $pageUid = (int)$properties['pageUid'];

        return $this->buildArgument($element, ['linkText'], $pageUid);
    }

    /**
     * Build translation arguments for label from array configuration.
     *
     * Returns the resolved arguments from property "additionalLinks". The
     * property consists of a key/value combination of "pageUid"/"linkText".
     *
     * @return string[]
     */
    private function buildArgumentsFromArrayConfiguration(GenericFormElement $element): array
    {
        if (!$this->hasAdditionalLinksConfigured($element)) {
            return [];
        }

        $properties = $element->getProperties();
        $arguments = [];

        foreach ($properties['additionalLinks'] as $pageUid => $linkText) {
            $arguments[$pageUid] = $this->buildArgument($element, ['additionalLinks', $pageUid], (int)$pageUid);
        }

        return $arguments;
    }

    /**
     * Build translation argument for label from given property path to link text.
     *
     * Returns the translation argument for the given property path. The property
     * path describes the path to the link text for the current argument, whereas
     * the pageUid describes the actual target page. If the pageUid is valid, this
     * method returns the generated link, otherwise the translated link text.
     *
     * @param GenericFormElement $element
     * @param string[] $linkTextPropertyPath
     * @param int $pageUid
     * @return string
     */
    private function buildArgument(GenericFormElement $element, array $linkTextPropertyPath, int $pageUid): string
    {
        $translatedLinkText = $this->translate($element, $linkTextPropertyPath);
        $additionalLinkConfiguration = $element->getRenderingOptions()['linkConfiguration'] ?? [];

        if ($pageUid <= 0) {
            return $translatedLinkText;
        }

        return $this->buildLinkFromPageUid($translatedLinkText, $pageUid, $additionalLinkConfiguration);
    }

    /**
     * Check whether renderable has additional links configured.
     *
     * Returns `true` if the current renderable has at least one "additional link"
     * configured (via property "additionalLinks").
     *
     * @param GenericFormElement $element
     * @return bool
     */
    private function hasAdditionalLinksConfigured(GenericFormElement $element): bool
    {
        $properties = $element->getProperties();

        return is_array($properties['additionalLinks'] ?? null) && $properties['additionalLinks'] !== [];
    }

    /**
     * Translate form element property by given path.
     *
     * @param RootRenderableInterface $renderable
     * @param string[] $propertyPath
     * @return string
     */
    private function translate(RootRenderableInterface $renderable, array $propertyPath): string
    {
        $translationService = GeneralUtility::makeInstance(TranslationService::class);
        $value = $translationService->translateFormElementValue($renderable, $propertyPath, $this->formRuntime);

        if (!is_string($value)) {
            return '';
        }

        return $value;
    }

    /**
     * Build typolink from given page UID and additional configuration.
     *
     * @param string $linkText
     * @param int $pageUid
     * @param array<string, string|int> $additionalAttributes
     * @return string
     */
    private function buildLinkFromPageUid(string $linkText, int $pageUid, array $additionalAttributes = []): string
    {
        if (!$pageUid) {
            return $linkText;
        }

        // Build typolink configuration from pageUid and additional attributes:
        // As the pageUid is a necessary part of the parameter configuration,
        // it cannot be overridden by $additionalAttributes. However one can
        // provide additional parameter configuration by making use of the
        // "parameter" key. This way one can disable the default link target
        // behaviour which falls back to "_blank" by providing an empty
        // value for the configuration key "parameter" or just setting any
        // different parameter values according to the TypoScript reference.
        $parameter = $pageUid . ' ';
        if (array_key_exists('parameter', $additionalAttributes)) {
            $parameter .= $additionalAttributes['parameter'];
        } else {
            $parameter .= '_blank';
        }
        $configuration = [
            'typolink.' => [
                'parameter' => trim($parameter),
                'forceAbsoluteUrl' => true,
            ],
        ];
        if ($additionalAttributes) {
            unset($additionalAttributes['parameter']);
            ArrayUtility::mergeRecursiveWithOverrule($configuration['typolink.'], $additionalAttributes);
        }

        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObject->start([], '');

        return $contentObject->stdWrap($linkText, $configuration) ?: $linkText;
    }

    /**
     * Check whether the given string needs character substitution.
     *
     * This method checks whether a given string contains substitution characters (%) which will be used
     * for character substitution using the `printf()` function. Substitution characters can be escaped
     * by an additional character (%%) and will be excluded from the check.
     *
     * @param string $value String to test for the need of character substitution
     * @return bool `true` if character substitution is needed, `false` otherwise
     * @see printf()
     */
    private static function needsCharacterSubstitution(string $value): bool
    {
        $filteredValue = $value;
        do {
            $filteredValue = str_replace('%%', '', $filteredValue);
        } while (str_contains($filteredValue, '%%'));
        return str_contains($filteredValue, '%');
    }
}
