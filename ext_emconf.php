<?php

$EM_CONF['form_element_linked_checkbox'] = [
    'title' => 'Form: Linked checkbox element',
    'description' => 'Adds a new form element which allows the editor to create a checkbox with a linked label text.',
    'category' => 'fe',
    'state' => 'stable',
    'author' => 'Björn Jacob, Elias Häußler, dreistrom.land',
    'author_email' => 'bjoern.jacob@tritum.de, elias@haeussler.dev, hello@dreistrom.land',
    'version' => '6.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
