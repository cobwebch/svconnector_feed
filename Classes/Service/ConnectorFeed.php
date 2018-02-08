<?php
namespace Cobweb\SvconnectorFeed\Service;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Cobweb\Svconnector\Exception\SourceErrorException;
use Cobweb\Svconnector\Service\ConnectorBase;
use Cobweb\Svconnector\Utility\ConnectorUtility;
use Cobweb\Svconnector\Utility\FileUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service that reads XML feeds for the "svconnector_feed" extension.
 *
 * @author Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package TYPO3
 * @subpackage tx_svconnectorfeed
 */
class ConnectorFeed extends ConnectorBase
{
    public $prefixId = 'tx_svconnectorfeed_sv1';        // Same as class name
    public $scriptRelPath = 'sv1/class.tx_svconnectorfeed_sv1.php';    // Path to this script relative to the extension dir.
    public $extKey = 'svconnector_feed';    // The extension key.
    protected $extConf; // Extension configuration

    /**
     * Verifies that the connection is functional
     * In the case of this service, it is always the case
     * It might fail for a specific file, but it is always available in general
     *
     * @return boolean TRUE if the service is available
     */
    public function init()
    {
        parent::init();
        $this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
        return true;
    }

    /**
     * This method calls the query method and returns the result as is,
     * i.e. the XML from the feed, but without any additional work performed on it
     *
     * @param array $parameters Parameters for the call
     * @return mixed Server response
     */
    public function fetchRaw($parameters)
    {
        $result = $this->query($parameters);
        // Implement post-processing hook
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processRaw'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processRaw'] as $className) {
                $processor = GeneralUtility::getUserObj($className);
                $result = $processor->processRaw($result, $this);
            }
        }

        return $result;
    }

    /**
     * This method calls the query and returns the results from the response as an XML structure
     *
     * @param array $parameters Parameters for the call
     * @return string XML structure
     */
    public function fetchXML($parameters)
    {
        // Get the feed, which is already in XML
        $xml = $this->query($parameters);
        // Implement post-processing hook
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processXML'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processXML'] as $className) {
                $processor = GeneralUtility::getUserObj($className);
                $xml = $processor->processXML($xml, $this);
            }
        }

        return $xml;
    }

    /**
     * This method calls the query and returns the results from the response as a PHP array
     *
     * @param array $parameters Parameters for the call
     * @return array PHP array
     */
    public function fetchArray($parameters)
    {
        // Get the data from the file
        $result = $this->query($parameters);
        $result = ConnectorUtility::convertXmlToArray($result);

        if (TYPO3_DLOG || $this->extConf['debug']) {
            GeneralUtility::devLog('Structured data', $this->extKey, -1, $result);
        }

        // Implement post-processing hook
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processArray'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processArray'] as $className) {
                $processor = GeneralUtility::getUserObj($className);
                $result = $processor->processArray($result, $this);
            }
        }
        return $result;
    }

    /**
     * Reads the content of the XML feed defined in the parameter and returns it as an array.
     *
     * NOTE: This method does not implement the "processParameters" hook, as it does not make sense in this case.
     *
     * @param array $parameters Parameters for the call
     * @return array Content of the feed
     * @throws SourceErrorException
     * @throws \Exception
     */
    protected function query($parameters)
    {

        if (TYPO3_DLOG || $this->extConf['debug']) {
            GeneralUtility::devLog('Call parameters', $this->extKey, -1, $parameters);
        }
        // Check if the feed's URI is defined
        if (empty($parameters['uri'])) {
            $message = $this->sL('LLL:EXT:svconnector_feed/Resources/Private/Language/locallang.xlf:no_feed_defined');
            if (TYPO3_DLOG || $this->extConf['debug']) {
                GeneralUtility::devLog($message, $this->extKey, 3);
            }
            throw new SourceErrorException(
                    $message,
                    1299257883
            );
        }

        $headers = null;
        if (array_key_exists('useragent', $parameters)) {
            $headers = array('User-Agent: ' . $parameters['useragent']);
        }

        $fileUtility = GeneralUtility::makeInstance(FileUtility::class);
        $data = $fileUtility->getFileContent($parameters['uri'], $headers);
        if ($data === false) {
            $message = sprintf(
                    $this->sL('LLL:EXT:svconnector_feed/Resources/Private/Language/locallang.xlf:feed_not_fetched'),
                    $parameters['uri'],
                    $fileUtility->getError()
            );
            if (TYPO3_DLOG || $this->extConf['debug']) {
                GeneralUtility::devLog($message, $this->extKey, 3);
            }
            throw new SourceErrorException(
                    $message,
                    1299257894
            );
        }
        // Check if the current charset is the same as the file encoding
        // Don't do the check if no encoding was defined
        // TODO: add automatic encoding detection by the reading the encoding attribute in the XML header
        if (empty($parameters['encoding'])) {
            $encoding = '';
            $isSameCharset = true;
        } else {
            // Standardize charset name and compare
            $encoding = $this->getCharsetConverter()->parse_charset($parameters['encoding']);
            $isSameCharset = $this->getCharset() === $encoding;
        }
        // If the charset is not the same, convert data
        if (!$isSameCharset) {
            $data = $this->getCharsetConverter()->conv($data, $encoding, $this->getCharset());
        }

        // Process the result if any hook is registered
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processResponse'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processResponse'] as $className) {
                $processor = GeneralUtility::getUserObj($className);
                $data = $processor->processResponse($data, $this);
            }
        }

        // Return the result
        return $data;
    }
}
