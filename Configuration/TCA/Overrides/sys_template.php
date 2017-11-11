<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'form_element_linked_checkbox',
    'Configuration/TypoScript',
    'form_element_linked_checkbox'
);
