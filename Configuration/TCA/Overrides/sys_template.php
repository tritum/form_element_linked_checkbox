<?php

defined('TYPO3') or die();

call_user_func(function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'form_element_linked_checkbox',
        'Configuration/TypoScript',
        'Linked checkbox configuration'
    );
});
