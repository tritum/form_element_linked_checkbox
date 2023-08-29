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
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

        if ((GeneralUtility::makeInstance(Typo3Version::class))->getMajorVersion() === 11) {
            ExtensionManagementUtility::addTypoScriptSetup(trim('
                module.tx_form {
                    settings {
                        yamlConfigurations {
                            1690355809 = EXT:form_element_linked_checkbox/Configuration/Yaml/FormSetupV11.yaml
                        }
                    }
                }
            '));
        }
    }

    public static function registerHooks(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterInitializeCurrentPage'][1571076908] = FormElementLinkResolverHook::class;
    }
}
