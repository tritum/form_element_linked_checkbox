<?php

$EM_CONF['form_element_linked_checkbox'] = [
    'title' => 'Form: Linked checkbox element',
    'description' => 'Adds a new form element which allows the editor to create a checkbox with a linked label text.',
    'category' => 'fe',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'author' => 'Björn Jacob, Elias Häußler',
    'author_email' => 'bjoern.jacob@tritum.de, elias@haeussler.dev',
    'version' => '3.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.30-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
