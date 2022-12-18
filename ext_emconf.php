<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Connector service - XML Feed',
    'description' => 'Connector service for XML files or RSS feeds',
    'category' => 'services',
    'version' => '3.0.0',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearcacheonload' => 1,
    'author' => 'Francois Suter (Idéative)',
    'author_email' => 'typo3@ideative.ch',
    'author_company' => '',
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '11.5.0-12.1.99',
                    'svconnector' => '4.0.0-0.0.0',
                ],
            'conflicts' =>
                [
                ],
            'suggests' =>
                [
                ],
        ],
];

