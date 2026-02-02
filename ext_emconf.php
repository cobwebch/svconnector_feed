<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Connector service - XML Feed',
    'description' => 'Connector service for XML files or RSS feeds',
    'category' => 'services',
    'version' => '5.0.0',
    'state' => 'stable',
    'clearcacheonload' => 1,
    'author' => 'Francois Suter (Idéative)',
    'author_email' => 'typo3@ideative.ch',
    'author_company' => '',
    'constraints' =>
        [
            'depends' =>
                [
                    'php' => '8.2.0-8.5.99',
                    'typo3' => '13.4.0-14.1.99',
                    'svconnector' => '6.0.0-0.0.0',
                ],
            'conflicts' =>
                [
                ],
            'suggests' =>
                [
                ],
        ],
];

