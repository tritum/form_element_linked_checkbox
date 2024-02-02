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

use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
use TYPO3\CMS\Form\Domain\Model\Renderable\RootRenderableInterface;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

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
            if ($renderable->getType() === $this->type) {
                $renderable->processCharacterSubstitution();
            }
        }

        return $currentPage;
    }

    /**
     * Resolve link in label for form elements with type LinkedCheckbox
     */
    public function beforeRendering(FormRuntime $formRuntime, RootRenderableInterface $renderable)
    {
        if ($renderable->getType() !== $this->type) {
            return;
        }

        // Process label one last time before rendering element.
        $renderable->processCharacterSubstitution();
        $this->cleanupProperties($renderable);
    }

    private function cleanupProperties(RootRenderableInterface $renderable)
    {
        // Reset custom properties in order to avoid additional
        // link rendering in template
        $renderable->setProperty('linkText', null);
        $renderable->setProperty('pageUid', null);
        $renderable->setProperty('additionalLinks', null);
    }
}
