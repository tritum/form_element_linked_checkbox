<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {
    if (TYPO3_MODE === 'BE') {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(trim('
            module.tx_form {
                settings {
                    yamlConfigurations {
                        1505042806 = EXT:form_element_linked_checkbox/Configuration/Yaml/FormSetup.yaml
                    }
                }
            }
        '));
    }

    $typo3Version = new \TYPO3\CMS\Core\Information\Typo3Version();
    if ($typo3Version->getMajorVersion() >= 10) {
        // load additional YAML configuration to load translation files differently for T3 v10
        if (TYPO3_MODE === 'BE') {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(trim('
            module.tx_form {
                settings {
                    yamlConfigurations {
                        1587917063 = EXT:form_element_linked_checkbox/Configuration/Yaml/FormSetupV10.yaml
                    }
                }
            }
        '));
        }
    }

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeRendering'][1571076908]
        = \TRITUM\FormElementLinkedCheckbox\Hooks\FormElementLinkResolverHook::class;
});
