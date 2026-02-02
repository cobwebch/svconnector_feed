<?php

declare(strict_types=1);

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

namespace Cobweb\SvconnectorFeed\Service;

use Cobweb\Svconnector\Attribute\AsConnectorService;
use Cobweb\Svconnector\Event\ProcessArrayDataEvent;
use Cobweb\Svconnector\Event\ProcessRawDataEvent;
use Cobweb\Svconnector\Event\ProcessResponseEvent;
use Cobweb\Svconnector\Event\ProcessXmlDataEvent;
use Cobweb\Svconnector\Exception\SourceErrorException;
use Cobweb\Svconnector\Service\ConnectorBase;
use Cobweb\Svconnector\Utility\ConnectorUtility;
use Cobweb\Svconnector\Utility\FileUtility;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service that reads XML feeds for the "svconnector_feed" extension.
 */
#[AsConnectorService(type: 'feed', name: 'XML/RSS feed connector')]
class ConnectorFeed extends ConnectorBase
{
    protected string $extensionKey = 'svconnector_feed';

    /**
     * Verifies that the connection is functional
     * In the case of this service, it is always the case
     * It might fail for a specific file, but it is always available in general
     *
     * @return bool TRUE if the service is available
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * Checks the connector configuration and returns notices, warnings or errors, if any.
     */
    public function checkConfiguration(): array
    {
        $result = parent::checkConfiguration();
        // The "uri" parameter is mandatory
        if (empty($this->parameters['uri'])) {
            $result[ContextualFeedbackSeverity::ERROR->value][] = $this->sL(
                'LLL:EXT:svconnector_feed/Resources/Private/Language/locallang.xlf:no_feed_defined'
            );
        }
        return $result;
    }

    /**
     * This method calls the query method and returns the result as is,
     * i.e. the XML from the feed, but without any additional work performed on it
     *
     * @throws \Exception
     */
    public function fetchRaw(): mixed
    {
        $result = $this->query();
        $event = $this->eventDispatcher->dispatch(
            new ProcessRawDataEvent($result, $this)
        );
        return $event->getData();
    }

    /**
     * This method calls the query and returns the results from the response as an XML structure
     *
     * @throws \Exception
     */
    public function fetchXML(): string
    {
        // Get the feed, which is already in XML
        $xml = $this->query();
        $event = $this->eventDispatcher->dispatch(
            new ProcessXmlDataEvent($xml, $this)
        );

        return $event->getData();
    }

    /**
     * This method calls the query and returns the results from the response as a PHP array
     *
     * @throws \Exception
     */
    public function fetchArray(): array
    {
        // Get the data from the file
        $result = $this->query();
        $result = ConnectorUtility::convertXmlToArray($result);

        $this->logger->info('Structured data', $result);

        $event = $this->eventDispatcher->dispatch(
            new ProcessArrayDataEvent($result, $this)
        );
        return $event->getData();
    }

    /**
     * Reads the content of the XML feed defined in the parameter and returns it as an array.
     *
     * @throws \Exception
     */
    protected function query(): mixed
    {
        $this->logger->info('Call parameters', $this->parameters);
        // Check the configuration
        $problems = $this->checkConfiguration();
        // Log all issues and raise error if any
        $this->logConfigurationCheck($problems);
        if (count($problems[ContextualFeedbackSeverity::ERROR->value]) > 0) {
            $message = '';
            foreach ($problems[ContextualFeedbackSeverity::ERROR->value] as $problem) {
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

        $headers = $this->parameters['headers'] ?? [];
        $fileUtility = GeneralUtility::makeInstance(FileUtility::class);
        $data = $fileUtility->getFileContent(
            $this->parameters['uri'],
            count($headers) > 0 ? $headers : null,
            $this->parameters['method'] ?? 'GET'
        );
        if ($data === false) {
            $message = sprintf(
                $this->sL('LLL:EXT:svconnector_feed/Resources/Private/Language/locallang.xlf:feed_not_fetched'),
                $this->parameters['uri'],
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
        if (empty($this->parameters['encoding'])) {
            $encoding = null;
            $isSameCharset = true;
        } else {
            $encoding = $this->parameters['encoding'];
            $isSameCharset = $this->getCharset() === $encoding;
        }
        // If the charset is not the same, convert data
        if (!$isSameCharset) {
            $data = mb_convert_encoding($data, $this->getCharset(), $encoding);
        }

        $event = $this->eventDispatcher->dispatch(
            new ProcessResponseEvent($data, $this)
        );

        // Return the result
        return $event->getResponse();
    }
}
