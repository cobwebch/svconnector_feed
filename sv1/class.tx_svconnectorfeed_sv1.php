<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Roberto Presedo (Cobweb) <typo3@cobweb.ch>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
*
* $Id: class.tx_svconnectorfeed_sv1.php 15769 2009-01-17 17:27:13Z presedo $
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(t3lib_extMgm::extPath('svconnector').'sv1/class.tx_svconnector_sv1.php');
require_once(t3lib_extMgm::extPath('svconnector_feed').'lib/rss_php.php');

/**
 * Service "CSV connector" for the "svconnector_feed" extension.
 *
 * @author	Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package	TYPO3
 * @subpackage	tx_svconnectorfeed
 */
class tx_svconnectorfeed_sv1 extends tx_svconnector_sv1 {
	public $prefixId = 'tx_svconnectorfeed_sv1';		// Same as class name
	public $scriptRelPath = 'sv1/class.tx_svconnectorfeed_sv1.php';	// Path to this script relative to the extension dir.
	public $extKey = 'svconnector_feed';	// The extension key.
	protected $extConf; // Extension configuration

	/**
	 * Verifies that the connection is functional
	 * In the case of CSV, it is always the case
	 * It might fail for a specific file, but it is always available in general
	 *
	 * @return	boolean		TRUE if the service is available
	 */
	public function init() {
		parent::init();
		if (!$this->lang) {
			if (isset($GLOBALS['LANG'])) {
				$this->lang = $GLOBALS['LANG'];
			}
			elseif (isset($GLOBALS['TSFE']->lang)) {
				$this->lang = $GLOBALS['TSFE']->lang;
			}
		}
		
		$this->lang->includeLLFile('EXT:'.$this->extKey.'/sv1/locallang.xml');
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
		return true;
	}

	/**
	 * This method calls the query method and returns the result as is,
	 * i.e. the parsed CSV data, but without any additional work performed on it
	 *
	 * @param	array	$parameters: parameters for the call
	 *
	 * @return	mixed	server response
	 */
	public function fetchRaw($parameters) {
		$result = $this->query($parameters);
		// Implement post-processing hook
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processRaw'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processRaw'] as $className) {
				$processor = &t3lib_div::getUserObj($className);
				$result = $processor->processRaw($result, $this);
			}
		}

		return $result;
	}

	/**
	 * This method calls the query and returns the results from the response as an XML structure
	 *
	 * @param	array	$parameters: parameters for the call
	 *
	 * @return	string	XML structure
	 */
	public function fetchXML($parameters) {
		// Get the data as an array
		$result = $this->fetchArray($parameters);
		// Transform result to XML
		$xml = t3lib_div::array2xml_cs($result);
		// Implement post-processing hook
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processXML'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processXML'] as $className) {
				$processor = &t3lib_div::getUserObj($className);
				$xml = $processor->processXML($xml, $this);
			}
		}
		
		return $xml;
	}

	/**
	 * This method calls the query and returns the results from the response as a PHP array
	 *
	 * @param	array	$parameters: parameters for the call
	 *
	 * @return	array	PHP array
	 */
	public function fetchArray($parameters) {
			// Get the data from the file
		$result = $this->query($parameters);

		if (TYPO3_DLOG || $this->extConf['debug']) {
			t3lib_div::devLog('Structured data', $this->extKey, -1, $result);
		}

		// Implement post-processing hook
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processArray'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processArray'] as $className) {
				$processor = &t3lib_div::getUserObj($className);
				$result = $processor->processArray($result, $this);
			}
		}
		return $result;
	}

	/**
	 * This method reads the content of the XML feed defined in the parameters
	 * and returns it as an array
	 *
	 * NOTE:	this method does not implement the "processParameters" hook,
	 *			as it does not make sense in this case
	 *
	 * @param	array	$parameters: parameters for the call
	 * @return	array	content of the feed
	 */
	protected function query($parameters) {


		$fileData = '';
		if (TYPO3_DLOG || $this->extConf['debug']) {
			t3lib_div::devLog('Call parameters', $this->extKey, -1, $parameters);
		}
 		// Check if the feeds is defined
		if (empty($parameters['feedurl'])) {
			if (TYPO3_DLOG || $this->extConf['debug']) {
				t3lib_div::devLog($this->lang->getLL('no_feed_defined'), $this->extKey, 3);
			}
		}
		else {

				// Load the XML feed and parse it
			$RSS_PHP = new rss_php;
			$RSS_PHP->load($parameters['feedurl']);
				// Get feed items
			$results_simple = $RSS_PHP->getItems();
			
				// If at leaste one attribute gets its value from a propertie, we load the full structure
			if (isset($parameters['properties_values'])) {
				$results_full = $RSS_PHP->getItems(true);
				if (TYPO3_DLOG || $this->extConf['debug']) {
					t3lib_div::devLog('Data from file (FULL)', $this->extKey, -1, $results_full);
				}
			}
			else {
				if (TYPO3_DLOG || $this->extConf['debug']) {
					t3lib_div::devLog('Data from file', $this->extKey, -1, $results_simple);
				}	
			}
			
			$x=0;
				// we define the values of each attribute
			foreach($results_simple as $items => $infos) {
				foreach ($infos as $attribute => $value) {
						// We check if the value of the attribute must be found in a propertie
					if (isset($parameters['properties_values'][$attribute])) {
						$results[$x][$attribute] = $results_full[$x][$attribute]['properties'][$parameters['properties_values'][$attribute]];
					}
					else
						$results[$x][$attribute] = $value;		
					}
					$x++;
				}
			}

			// Checks if a minimal quantity of items has been found
			if ($parameters['minimalQty'] > 0 && $x < $parameters['minimalQty']) {
				if (TYPO3_DLOG || $this->extConf['debug']) t3lib_div::devLog(sprintf($this->lang->getLL('minimal_qty_not_reached'), $x, $parameters['minimalQty']), $this->extKey, 3);
				$this->errorPush(T3_ERR_SV_BAD_RESPONSE,sprintf($this->lang->getLL('minimal_qty_not_reached'), $x, $parameters['minimalQty']));
				die(sprintf($this->lang->getLL('minimal_qty_not_reached'), $x, $parameters['minimalQty']));
			}

/* @todo : MAKE AN ERROR IF FEED NO FOUND

			if (TYPO3_DLOG || $this->extConf['debug']) {
				t3lib_div::devLog(sprintf($this->lang->getLL('feed_not_found'), $parameters['feedurl']), $this->extKey, 3);
			}
*/

		// Process the result if any hook is registered
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processResponse'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processResponse'] as $className) {
				$processor = &t3lib_div::getUserObj($className);
				$results = $processor->processResponse($results, $this);
			}
		}

		// Return the result
		return $results;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/svconnector_feed/sv1/class.tx_svconnectorfeed_sv1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/svconnector_feed/sv1/class.tx_svconnectorfeed_sv1.php']);
}

?>