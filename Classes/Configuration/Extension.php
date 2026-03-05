<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "form_element_linked_checkbox".
 *
 * Copyright (C) 2021 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace TRITUM\FormElementLinkedCheckbox\Configuration;

use TRITUM\FormElementLinkedCheckbox\Hooks\FormElementLinkResolverHook;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Extension
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 * @internal
 */
final class Extension
{
    public const KEY = 'form_element_linked_checkbox';

    public static function addTypoScriptSetup(): void
    {
        ExtensionManagementUtility::addTypoScriptSetup(trim('
            module.tx_form {
                settings {
                    yamlConfigurations {
                        1505042806 = EXT:form_element_linked_checkbox/Configuration/Yaml/FormSetup.yaml
                    }
                }
            }
        '));
    }

    /**
     * @todo Remove hook when dropping v13 support.
     * @see https://docs.typo3.org/c/typo3/cms-core/main/en-us//Changelog/14.0/Breaking-107566-RemovedAfterInitializeCurrentPageHook.html
     * @see https://docs.typo3.org/c/typo3/cms-core/main/en-us//Changelog/14.0/Feature-107566-IntroducePSR14AfterCurrentPageIsResolvedEvent.html#feature-107566-1759226649
     */
    public static function registerHooks(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterInitializeCurrentPage'][1571076908] = FormElementLinkResolverHook::class;
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeRendering'][1571076908] = FormElementLinkResolverHook::class;
    }
}
