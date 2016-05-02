.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developer:

Developer's manual
------------------

Reading a XML file using the Feed/XML connector service is a really
easy task. The first step is to get the proper service object:

.. code-block:: php

	$services = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::findService('connector', 'feed');
	if ($services === FALSE) {
		// Issue an error
	} else {
		$connector = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstanceService('connector', 'feed');
	}

On the first line, you get a list of all services that are of type
“connector” and subtype “feed”. If the result if false, it means no
appropriate services were found and you probably want to issue an
error message.

On the contrary you are assured that there's at least one valid
service and you can get an instance of it by calling
:code:`\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstanceService()` .

The next step is simply to call the appropriate method from the API –
with the right parameters – depending on which format you want to have
in return. For example:

.. code-block:: php

	$parameters = array(
		'uri' => 'https://typo3.org/xml-feeds/rss.xml',
		'encoding' => 'utf-8',
	);
	$data = $connector->fetchXML($parameters);

This will return the XML from the feed as a string. The :code:`fetchRaw()` will return the same.

The :code:`fetchArray()` method returns an array version of the XML
transformed using :code:`\Cobweb\Svconnector\Utility\ConnectorUtility::convertXmlToArray()`.
The returned array has a rather complex structure,
but it ensures that no information is lost.
