<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

t3lib_extMgm::addService($_EXTKEY,  'connector' /* sv type */,  'tx_svconnectorfeed_sv1' /* sv key */,
		array(

			'title' => 'RSS Feed connector',
			'description' => 'Connector service to get RSS feeds',

			'subtype' => 'feed',

			'available' => TRUE,
			'priority' => 50,
			'quality' => 50,

			'os' => '',
			'exec' => '',

			'classFile' => t3lib_extMgm::extPath($_EXTKEY) . 'sv1/class.tx_svconnectorfeed_sv1.php',
			'className' => 'tx_svconnectorfeed_sv1',
		)
	);
?>