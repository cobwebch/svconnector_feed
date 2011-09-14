<?php

########################################################################
# Extension Manager/Repository config file for ext "svconnector_feed".
#
# Auto generated 14-09-2011 09:18
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Connector service - FEED',
	'description' => 'Connector service for XML files or RSS feeds',
	'category' => 'services',
	'shy' => 0,
	'version' => '1.1.0',
	'dependencies' => 'svconnector',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Francois Suter (Cobweb)',
	'author_email' => 'typo3@cobweb.ch',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.3.0-0.0.0',
			'svconnector' => '2.0.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:10:{s:9:"ChangeLog";s:4:"0df3";s:10:"README.txt";s:4:"4f8f";s:16:"ext_autoload.php";s:4:"c230";s:21:"ext_conf_template.txt";s:4:"ef02";s:12:"ext_icon.gif";s:4:"d043";s:17:"ext_localconf.php";s:4:"cb19";s:14:"doc/manual.pdf";s:4:"045f";s:14:"doc/manual.sxw";s:4:"d483";s:36:"sv1/class.tx_svconnectorfeed_sv1.php";s:4:"2a34";s:17:"sv1/locallang.xml";s:4:"c67c";}',
	'suggests' => array(
	),
);

?>