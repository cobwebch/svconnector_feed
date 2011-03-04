<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Francois Suter (Cobweb) <typo3@cobweb.ch>
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
 * Service that reads XML feeds for the "svconnector_feed" extension.
 *
 * @author		Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package		TYPO3
 * @subpackage	tx_svconnectorfeed
 */
class tx_svconnectorfeed_sv1 extends tx_svconnector_base {
	public $prefixId = 'tx_svconnectorfeed_sv1';		// Same as class name
	public $scriptRelPath = 'sv1/class.tx_svconnectorfeed_sv1.php';	// Path to this script relative to the extension dir.
	public $extKey = 'svconnector_feed';	// The extension key.
	protected $extConf; // Extension configuration

	/**
	 * Verifies that the connection is functional
	 * In the case of this service, it is always the case
	 * It might fail for a specific file, but it is always available in general
	 *
	 * @return	boolean		TRUE if the service is available
	 */
	public function init() {
		parent::init();
		$this->lang->includeLLFile('EXT:' . $this->extKey . '/sv1/locallang.xml');
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
		return true;
	}

	/**
	 * This method calls the query method and returns the result as is,
	 * i.e. the XML from the feed, but without any additional work performed on it
	 *
	 * @param	array	$parameters: parameters for the call
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
	 * @return	string	XML structure
	 */
	public function fetchXML($parameters) {
			// Get the feed, which is already in XML
		$xml = $this->query($parameters);
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
		$result = tx_svconnector_utility::convertXmlToArray($result);

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

		if (TYPO3_DLOG || $this->extConf['debug']) {
			t3lib_div::devLog('Call parameters', $this->extKey, -1, $parameters);
		}
	 		// Check if the feed's URI is defined
		if (empty($parameters['uri'])) {
			$message = $this->lang->getLL('no_feed_defined');
			if (TYPO3_DLOG || $this->extConf['debug']) {
				t3lib_div::devLog($message, $this->extKey, 3);
			}
			throw new Exception($message, 1299257883);
		} else {
			$report = array();
			$data = t3lib_div::getURL($parameters['uri'], 0, FALSE, $report);
			if (!empty($report['message'])) {
				$message = sprintf($this->lang->getLL('feed_not_found'), $parameters['uri'], $report['message']);
				if (TYPO3_DLOG || $this->extConf['debug']) {
					t3lib_div::devLog($message, $this->extKey, 3, $report);
				}
				throw new Exception($message, 1299257894);
			}
				// Check if the current charset is the same as the file encoding
				// Don't do the check if no encoding was defined
				// TODO: add automatic encoding detection by the reading the encoding attribute in the XML header
			if (empty($parameters['encoding'])) {
				$isSameCharset = TRUE;
			} else {
					// Standardize charset name and compare
				$encoding = $this->lang->csConvObj->parse_charset($parameters['encoding']);
				$isSameCharset = $this->lang->charSet == $encoding;
			}
				// If the charset is not the same, convert data
				// NOTE: example values for testing conversion:
				//		uri = http://www.rususa.com/tools/rss/feed.asp-rss-newsrus
				//		encoding = windows-1251
			if (!$isSameCharset) {
				$data = $this->lang->csConvObj->conv($data, $encoding, $this->lang->charSet);
			}
		}

			// Process the result if any hook is registered
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processResponse'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processResponse'] as $className) {
				$processor = &t3lib_div::getUserObj($className);
				$data = $processor->processResponse($data, $this);
			}
		}

			// Return the result
		return $data;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/svconnector_feed/sv1/class.tx_svconnectorfeed_sv1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/svconnector_feed/sv1/class.tx_svconnectorfeed_sv1.php']);
}
?>