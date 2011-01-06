<?php

########################################################################
# Extension Manager/Repository config file for ext: "svconnector_feed"
#
# Auto generated 22-03-2010 11:33
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Connector service - FEED',
	'description' => 'Connector service for XML files or RSS feeds',
	'category' => 'services',
	'shy' => 0,
	'version' => '0.2.1',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
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
	'_md5_values_when_last_written' => 'a:8:{s:9:"ChangeLog";s:4:"a0fb";s:10:"README.txt";s:4:"ee2d";s:21:"ext_conf_template.txt";s:4:"ef02";s:12:"ext_icon.gif";s:4:"c460";s:17:"ext_localconf.php";s:4:"8c7f";s:14:"doc/manual.sxw";s:4:"3f74";s:35:"sv1/class.tx_svconnectorfeed_sv1.php";s:4:"e33a";s:17:"sv1/locallang.xml";s:4:"e842";}',
);

?>