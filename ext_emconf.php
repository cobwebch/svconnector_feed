<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Connector service - XML Feed',
    'description' => 'Connector service for XML files or RSS feeds',
    'category' => 'services',
    'version' => '4.1.0',
    'state' => 'stable',
    'clearcacheonload' => 1,
    'author' => 'Francois Suter (Idéative)',
    'author_email' => 'typo3@ideative.ch',
    'author_company' => '',
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '12.4.0-13.4.99',
                    'svconnector' => '5.0.0-0.0.0',
                ],
            'conflicts' =>
                [
                ],
            'suggests' =>
                [
                ],
        ],
];

