<?php
declare(strict_types=1);
namespace TRITUM\FormElementLinkedCheckbox\Hooks;

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

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;
use TYPO3\CMS\Form\Domain\Model\Renderable\RootRenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Service\TranslationService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Form rendering hook to resolve link in label of LinkedCheckbox elements.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
class FormElementLinkResolverHook
{
    /**
     * @var string Form element type to match
     */
    protected $type = 'LinkedCheckbox';

    /**
     * Resolve link in label of form elements with type LinkedCheckbox.
     *
     * @param FormRuntime $formRuntime
     * @param RootRenderableInterface $renderable
     */
    public function beforeRendering(FormRuntime $formRuntime, RootRenderableInterface $renderable)
    {
        $label = $this->translate($renderable, 'label', $formRuntime);

        // Only process linkText parsing if $renderable matches given type
        // and form element label contains any argument flags such as %s.
        // This also checks if one tries to use the percent sign as regular
        // character instead of a flag marked for inserting the translated
        // linkText. It needs to be set as double-percent (%%) substring.
        if (
            !$renderable instanceof GenericFormElement ||
            $renderable->getType() !== $this->type ||
            !self::needsCharacterSubstitution($label)
        ) {
            return;
        }

        $properties = $renderable->getProperties();
        $pageUids = $this->extractUids($properties);
        $linkTexts = $this->extractLinkTexts($properties);

        // Build link if pageUid is valid
        if (count($pageUids) > 0 && count($linkTexts) > 0) {
            $additionalLinkConfiguration = $renderable->getRenderingOptions()['linkConfiguration'] ?? [];
            $links = [];
            for ($i = 0; $i < count($pageUids); $i++) {
                if (!key_exists($i, $linkTexts) || !key_exists($i, $pageUids)) {
                    continue;
                }
                $links[] = $this->buildLinkFromPageUid($linkTexts[$i], $pageUids[$i], $additionalLinkConfiguration);
            }
        } else {
            $links = $properties['linkText'];
        }

        // Provide translated link as argument for the form element label
        $renderable->setRenderingOption('translation', [
            'arguments' => [
                'label' => [
                    $links,
                ],
            ],
        ]);

        // Override final label (with translated link) as well
        // as it will be used as default value if no translation is provided
        $translatedLabel = vsprintf($label, $links);
        if ($translatedLabel === false) {
            throw new \Exception('1593536213: Please provide the same amount of values for the substitution as placeholders in the linked checkbox form element for the "' . $formRuntime->getLabel() . '" form.');
        }
        $renderable->setLabel($translatedLabel);

        // Reset linkText and pageUid properties in order
        // to avoid additional link rendering in template
        $renderable->setProperty('linkText', null);
        $renderable->setProperty('pageUid', null);

        // Set fallback value to original property values
        // to allow other hooks making use of these ones
        $renderable->setProperty('_label', $label);
        $renderable->setProperty('_linkText', implode('|', $linkTexts));
        $renderable->setProperty('_pageUid', implode('|', $pageUids));
    }

    /**
     * Translate form element property.
     *
     * @param RootRenderableInterface $renderable
     * @param string $property
     * @param FormRuntime $formRuntime
     * @return string
     */
    protected function translate(RootRenderableInterface $renderable, string $property, FormRuntime $formRuntime): string
    {
        return (string) TranslationService::getInstance()->translateFormElementValue($renderable, [$property], $formRuntime);
    }

    /**
     * Build typolink from given page UID and additional configuration.
     *
     * @param $linkText
     * @param int $pageUid
     * @param array $additionalAttributes
     * @return string
     */
    protected function buildLinkFromPageUid(string $linkText, int $pageUid, array $additionalAttributes = []): string
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
            $parameter .= (string) $additionalAttributes['parameter'];
        } else {
            $parameter .= '_blank';
        }
        $configuration = [
            'typolink.' => [
                'parameter' => trim($parameter),
            ],
        ];
        if ($additionalAttributes) {
            unset($additionalAttributes['parameter']);
            ArrayUtility::mergeRecursiveWithOverrule($configuration['typolink.'], $additionalAttributes);
        }

        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObject->start([], '');
        $link = $contentObject->stdWrap($linkText, $configuration);

        return $link;
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
    protected static function needsCharacterSubstitution(string $value): bool
    {
        $filteredValue = $value;
        do {
            $filteredValue = str_replace('%%', '', $filteredValue);
        } while(strpos($filteredValue, '%%') !== false);
        return strpos($filteredValue, '%') !== false;
    }

    private function extractUids(array $properties): array
    {
        return GeneralUtility::intExplode('|', $properties['pageUid']);
    }

    private function extractLinkTexts(array $properties): array
    {
        return GeneralUtility::trimExplode('|', $properties['linkText']);
    }
}
