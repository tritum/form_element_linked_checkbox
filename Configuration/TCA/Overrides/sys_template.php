<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

call_user_func(function (): void {
    ExtensionManagementUtility::addStaticFile(
        'form_element_linked_checkbox',
        'Configuration/TypoScript',
        'Linked checkbox configuration'
    );
});
