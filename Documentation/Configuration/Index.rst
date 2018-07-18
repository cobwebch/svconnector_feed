.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _configuration:

Configuration
-------------

The various "fetch" methods of the Feed/XML connector all take the same
parameters:

+-----------------+---------------+-------------------------------------------------------------------------------+
| Parameter       | Data type     | Description                                                                   |
+=================+===============+===============================================================================+
| uri             | string        | URI of the XML file to read. This may be any of the following syntaxes:       |
|                 |               |                                                                               |
|                 |               | - absolute file path: :file:`/var/foo/web/fileadmin/import/bar.xml`           |
|                 |               |   (within the TYPO3 root path or :code:`TYPO3_CONF_VARS[BE][lockRootPath]`)   |                  |
|                 |               | - file path relative to the TYPO3 root:                                       |
|                 |               |   :file:`fileadmin/import/foo.xml`                                            |
|                 |               | - file path using :code:`EXT:`:                                               |
|                 |               |   :file:`EXT:foo/Resources/Private/Data/bar.xml`                              |
|                 |               | - fully qualified URL, e.g. :file:`http://www.example.com/foo.xml`            |
|                 |               | - FAL reference with storage ID and file identifier:                          |
|                 |               |   :file:`FAL:2:/foo.xml`                                                      |
|                 |               | - custom syntax: :file:`MYKEY:whatever_you_want`, see                         |
|                 |               |   :ref:`Connector Services <svconnector:developers-utilities-reading-files>`  |
+-----------------+---------------+-------------------------------------------------------------------------------+
| encoding        | string        | Encoding of the data found in the file. This value must match any of          |
|                 |               | the encoding values or their synonyms found in class                          |
|                 |               | :code:`\TYPO3\CMS\Core\Charset\CharsetConverter`.                             |
|                 |               | Note that this means pretty much all the usual encodings.                     |
|                 |               | If unsure look at array                                                       |
|                 |               | :code:`\TYPO3\CMS\Core\Charset\CharsetConverter::synonyms`.                   |
|                 |               |                                                                               |
|                 |               | .. warning::                                                                  |
|                 |               |                                                                               |
|                 |               |    If your are aiming for the array format (i.e. calling                      |
|                 |               |    :code:`fetchArray()`, you should not define this property. Indeed the      |
|                 |               |    :code:`\Cobweb\Svconnector\Utility\ConnectorUtility::convertXmlToArray()`  |
|                 |               |    which is used in this case relies on the SimpleXML library, which          |
|                 |               |    already takes care of the encoding conversion. To avoid a double           |
|                 |               |    encoding just ignore this property.                                        |
+-----------------+---------------+-------------------------------------------------------------------------------+
| useragent       | string        | User agent to fake. This is sometimes necessary to bypass access              |
|                 |               | restrictions on some sites. Don't include the "User-Agent:" part of           |
|                 |               | the header.                                                                   |
|                 |               |                                                                               |
|                 |               | |                                                                             |
|                 |               |                                                                               |
|                 |               | **Examples:**                                                                 |
|                 |               |                                                                               |
|                 |               | Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US;                        |
|                 |               |                                                                               |
|                 |               | rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13                                    |
+-----------------+---------------+-------------------------------------------------------------------------------+
