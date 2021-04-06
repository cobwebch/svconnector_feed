<?php

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'svconnector_feed',
    // Service type
    'connector',
    // Service key
    'tx_svconnectorfeed_sv1',
    [
        'title' => 'RSS Feed connector',
        'description' => 'Connector service to get RSS feeds',

        'subtype' => 'feed',

        'available' => true,
        'priority' => 50,
        'quality' => 50,

        'os' => '',
        'exec' => '',

        'className' => \Cobweb\SvconnectorFeed\Service\ConnectorFeed::class,
    ]
);
