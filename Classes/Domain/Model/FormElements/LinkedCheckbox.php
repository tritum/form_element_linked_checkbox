<?php

namespace TRITUM\FormElementLinkedCheckbox\Domain\Model\FormElements;

use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Model\FormElements\AbstractFormElement;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Service\TranslationService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class LinkedCheckbox extends AbstractFormElement
{
    private $runtime;

    /**
     * Flag to force returning the unprocessed label of the element.
     */
    private $realLabel = false;

    public function getLabel(): string
    {
        if ($this->realLabel or empty($this->getProperties()['_processedLabel'])) {
            $this->realLabel = false;
            return parent::getLabel();
        }

        return $this->getProperties()['_processedLabel'];
    }

    public function setOptions(array $options, bool $resetValidators = false)
    {
        parent::setOptions($options, $resetValidators);

        // Currently, $resetValidators is only `true` when variants are being
        // applied. Its the only place where we can process label before finishers
        // without modifying core classes.
        if ($resetValidators) {
            $this->processCharacterSubstitution();
        }
    }

    public function processCharacterSubstitution()
    {
        $this->realLabel = true;
        $label = $this->translate(['label']);

        $properties = $this->getProperties();

        // Check if form element label contains any argument flags such as %s.
        // This also checks if one tries to use the percent sign as regular
        // character instead of a flag marked for inserting the translated
        // linkText. It needs to be set as double-percent (%%) substring.
        // If character substitution is NOT requested, enforce the link to
        // be prepended to the label text.
        if (!$this->needsCharacterSubstitution($label)) {
            $label .= ' %s';
        }

        // Resolve all label arguments and merge them together in order to
        // use it for later translation of the label. The following
        // configuration methods are considered:
        // - "single configuration" via properties pageUid / linkText
        // - "array configuration" via property "additionalLinks"
        $singleLinkArgument = $this->buildArgumentFromSingleConfiguration();
        $additionalLinkArguments = $this->buildArgumentsFromArrayConfiguration();
        $labelArguments = array_merge([$singleLinkArgument], $additionalLinkArguments);

        // Provide translated link as argument for the form element label
        $this->setRenderingOption('translation', [
            'arguments' => [
                'label' => $labelArguments,
            ],
        ]);

        // Run translation again and override final label
        // (with translated links) as well as it will be used
        // as default value if no translation is provided
        $translatedLabel = vsprintf($label, $labelArguments);
        if (is_string($translatedLabel)) {
            // $this->setLabel($translatedLabel);
            $this->setProperty('_processedLabel', $translatedLabel);
        }

        // Set fallback value to original property values
        // to allow other hooks making use of these ones
        $this->setProperty('_label', $label);
        $this->setProperty('_linkText', $singleLinkArgument);
        $this->setProperty('_pageUid', (int)$properties['pageUid']);
        $this->setProperty('_additionalLinks', $additionalLinkArguments);
        $this->setProperty('_linksProcessed', true);
    }

    private function translate(array $propertyPath)
    {
        // `FormRuntime` is needed for `TranslationService`, but only
        // to access configured translation files and the form identifer
        // from `FormDefinition`.
        if (!$this->runtime) {
            $this->runtime = GeneralUtility::makeInstance(FormRuntime::class);
            $this->runtime->setFormDefinition($this->getRootForm());
        }

        $translationService = GeneralUtility::makeInstance(TranslationService::class);
        $value = $translationService->translateFormElementValue($this, $propertyPath, $this->runtime);

        if (!is_string($value)) {
            return '';
        }

        return $value;
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
    private function needsCharacterSubstitution(string $value): bool
    {
        $filteredValue = $value;
        do {
            $filteredValue = str_replace('%%', '', $filteredValue);
        } while (str_contains($filteredValue, '%%'));
        return str_contains($filteredValue, '%');
    }

    /**
     * Build translation argument for label from single configuration.
     *
     * Returns the resolved argument from properties "pageUid" and "linkText"
     * (default configuration).
     *
     * @return string
     */
    private function buildArgumentFromSingleConfiguration(): string
    {
        $properties = $this->getProperties();
        $pageUid = (int)$properties['pageUid'];

        return $this->buildArgument(['linkText'], $pageUid);
    }

    /**
     * Build translation arguments for label from array configuration.
     *
     * Returns the resolved arguments from property "additionalLinks". The
     * property consists of a key/value combination of "pageUid"/"linkText".
     *
     * @return string[]
     */
    private function buildArgumentsFromArrayConfiguration(): array
    {
        if (!$this->hasAdditionalLinksConfigured()) {
            return [];
        }

        $properties = $this->getProperties();
        $arguments = [];

        foreach ($properties['additionalLinks'] as $pageUid => $linkText) {
            $arguments[$pageUid] = $this->buildArgument(['additionalLinks', $pageUid], (int)$pageUid);
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
     * @param string[] $linkTextPropertyPath
     * @param int $pageUid
     * @return string
     */
    private function buildArgument(array $linkTextPropertyPath, int $pageUid): string
    {
        $translatedLinkText = $this->translate($linkTextPropertyPath);
        $additionalLinkConfiguration = $this->getRenderingOptions()['linkConfiguration'] ?? [];

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
     * @return bool
     */
    private function hasAdditionalLinksConfigured(): bool
    {
        $properties = $this->getProperties();

        return is_array($properties['additionalLinks'] ?? null) && $properties['additionalLinks'] !== [];
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
}
