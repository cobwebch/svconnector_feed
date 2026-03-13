<?php

declare(strict_types=1);

namespace Cobweb\SvconnectorFeed\Functional\Tests;

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
use Cobweb\SvconnectorFeed\Service\ConnectorFeed;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Testcase for the Feed Connector service.
 */
class ConnectorFeedTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/svconnector',
        'typo3conf/ext/svconnector_feed',
    ];

    protected ConnectorFeed $subject;

    /**
     * Sets up the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();
        try {
            $this->subject = GeneralUtility::makeInstance(ConnectorFeed::class);
        } catch (\Exception $e) {
            self::markTestSkipped($e->getMessage());
        }
    }

    /**
     * Provides references to CSV files to read and expected output.
     *
     * @return array
     */
    public static function sourceDataProvider(): array
    {
        return [
            'UTF-8 data' => [
                'parameters' => [
                    'uri' => 'EXT:svconnector_feed/Tests/Functional/Fixtures/data_utf8.xml',
                ],
                'result' => <<<EOT
<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<items>
	<item>
		<name>Porte interdùm lacîna c'est euismod.</name>
	</item>
</items>
EOT

            ],
            'ISO-8859-1 data with explicit encoding' => [
                'parameters' => [
                    'uri' => 'EXT:svconnector_feed/Tests/Functional/Fixtures/data_latin1.xml',
                    'encoding' => 'iso-8859-1',
                ],
                'result' => <<<EOT
<?xml version="1.0" encoding="ISO-8859-1" standalone="no"?>
<items>
	<item>
		<name>Porte interdùm lacîna c'est euismod.</name>
	</item>
</items>
EOT
            ],
            'ISO-8859-1 data with implicit encoding' => [
                'parameters' => [
                    'uri' => 'EXT:svconnector_feed/Tests/Functional/Fixtures/data_latin1.xml',
                ],
                'result' => <<<EOT
<?xml version="1.0" encoding="ISO-8859-1" standalone="no"?>
<items>
	<item>
		<name>Porte interdùm lacîna c'est euismod.</name>
	</item>
</items>
EOT
            ],
        ];
    }

    /**
     * Read test XML files and check the resulting content against an expected structure.
     *
     * @param array $parameters List of connector parameters
     * @param string $result Expected array structure
     * @throws \Exception
     */
    #[Test] #[DataProvider('sourceDataProvider')]
    public function readingXmlFileIntoString(array $parameters, string $result): void
    {
        $this->subject->setParameters($parameters);
        $data = $this->subject->fetchXML();
        self::assertSame($result, $data);
    }

    #[Test]
    public function readingUnknownFileThrowsException(): void
    {
        $this->expectException(SourceErrorException::class);
        $this->subject->setParameters(
            [
                'filename' => 'foobar.xml',
            ]
        );
        $this->subject->fetchXML();
    }
}
