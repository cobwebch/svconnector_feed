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

   $registry = GeneralUtility::makeInstance(\Cobweb\Svconnector\Registry\ConnectorRegistry::class);
   $connector = $registry->getServiceForType('feed');

An additional step could be to check if the service is indeed available,
by calling :php:`$connector->isAvailable()`, although - in this particular
case - the Feed/XML connector service is always available.

The next step is simply to call the appropriate method from the API –
with the right parameters – depending on which format you want to have
in return. For example:

.. code-block:: php

	$parameters = [
		'uri' => 'https://typo3.org/xml-feeds/rss.xml',
		'encoding' => 'utf-8',
	];
	$data = $connector->fetchXML($parameters);

This will return the XML from the feed as a string. The :code:`fetchRaw()` method will return the same.

The :code:`fetchArray()` method returns an array version of the XML
transformed using :code:`\Cobweb\Svconnector\Utility\ConnectorUtility::convertXmlToArray()`.
The returned array has a rather complex structure,
but it ensures that no information is lost.
