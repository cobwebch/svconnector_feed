<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "svconnector_feed".
 *
 * Auto generated 05-04-2017 17:54
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
        'title' => 'Connector service - XML Feed',
        'description' => 'Connector service for XML files or RSS feeds',
        'category' => 'services',
        'version' => '2.2.3',
        'state' => 'stable',
        'uploadfolder' => 0,
        'createDirs' => '',
        'clearcacheonload' => 1,
        'author' => 'Francois Suter (Cobweb)',
        'author_email' => 'typo3@cobweb.ch',
        'author_company' => '',
        'constraints' =>
                [
                        'depends' =>
                                [
                                        'typo3' => '7.6.0-9.99.99',
                                        'svconnector' => '3.2.3-0.0.0',
                                ],
                        'conflicts' =>
                                [
                                ],
                        'suggests' =>
                                [
                                ],
                ],
];

