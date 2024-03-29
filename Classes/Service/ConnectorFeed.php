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
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service that reads XML feeds for the "svconnector_feed" extension.
 */
class ConnectorFeed extends ConnectorBase
{
    protected string $extensionKey = 'svconnector_feed';

    protected string $type = 'feed';

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns the class as a string. Seems to be needed by phpunit when an exception occurs during a test run.
     *
     * @return string
     */
    public function __toString()
    {
        return 'ConnectorFeed';
    }

    public function getName(): string
    {
        return 'XML/RSS feed connector';
    }

    /**
     * Verifies that the connection is functional
     * In the case of this service, it is always the case
     * It might fail for a specific file, but it is always available in general
     *
     * @return boolean TRUE if the service is available
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * Checks the connector configuration and returns notices, warnings or errors, if any.
     *
     * @param array $parameters Connector call parameters
     * @return array
     */
    public function checkConfiguration(array $parameters = []): array
    {
        $result = parent::checkConfiguration($parameters);
        // The "uri" parameter is mandatory
        if (empty($parameters['uri'])) {
            $result[AbstractMessage::ERROR][] = $this->sL('LLL:EXT:svconnector_feed/Resources/Private/Language/locallang.xlf:no_feed_defined');
        }
        return $result;
    }

    /**
     * This method calls the query method and returns the result as is,
     * i.e. the XML from the feed, but without any additional work performed on it
     *
     * @param array $parameters Parameters for the call
     * @return mixed Server response
     * @throws \Exception
     */
    public function fetchRaw(array $parameters = [])
    {
        $result = $this->query($parameters);
        // Implement post-processing hook
        $hooks = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processRaw'] ?? null;
        if (is_array($hooks)) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processRaw'] as $className) {
                $processor = GeneralUtility::makeInstance($className);
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
     * @throws \Exception
     */
    public function fetchXML(array $parameters = []): string
    {
        // Get the feed, which is already in XML
        $xml = $this->query($parameters);
        // Implement post-processing hook
        $hooks = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processXML'] ?? null;
        if (is_array($hooks)) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processXML'] as $className) {
                $processor = GeneralUtility::makeInstance($className);
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
     * @throws \Exception
     */
    public function fetchArray(array $parameters = []): array
    {
        // Get the data from the file
        $result = $this->query($parameters);
        $result = ConnectorUtility::convertXmlToArray($result);

        $this->logger->info('Structured data', $result);

        // Implement post-processing hook
        $hooks = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processArray'] ?? null;
        if (is_array($hooks)) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processArray'] as $className) {
                $processor = GeneralUtility::makeInstance($className);
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
     * @return mixed Content of the feed
     * @throws \Exception
     */
    protected function query(array $parameters = [])
    {

        $this->logger->info('Call parameters', $parameters);
        // Check the configuration
        $problems = $this->checkConfiguration($parameters);
        // Log all issues and raise error if any
        $this->logConfigurationCheck($problems);
        if (count($problems[AbstractMessage::ERROR]) > 0) {
            $message = '';
            foreach ($problems[AbstractMessage::ERROR] as $problem) {
                if ($message !== '') {
                    $message .= "\n";
                }
                $message .= $problem;
            }
            $this->raiseError(
                    $message,
                    1299257883,
                    [],
                    SourceErrorException::class
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
            $this->raiseError(
                    $message,
                    1299257894,
                    [],
                    SourceErrorException::class
            );
        }
        // Check if the current charset is the same as the file encoding
        // Don't do the check if no encoding was defined
        // TODO: add automatic encoding detection by reading the encoding attribute in the XML header
        if (empty($parameters['encoding'])) {
            $encoding = '';
            $isSameCharset = true;
        } else {
            // Standardize charset name and compare
            $encoding = $parameters['encoding'];
            $isSameCharset = $this->getCharset() === $encoding;
        }
        // If the charset is not the same, convert data
        if (!$isSameCharset) {
            $data = $this->getCharsetConverter()->conv($data, $encoding, $this->getCharset());
        }

        // Process the result if any hook is registered
        $hooks = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processResponse'] ?? null;
        if (is_array($hooks)) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processResponse'] as $className) {
                $processor = GeneralUtility::makeInstance($className);
                $data = $processor->processResponse($data, $this);
            }
        }

        // Return the result
        return $data;
    }
}
