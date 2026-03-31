.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: /Includes.rst.txt


.. _installation:

Installation
------------

Install this extension and you can start using its API for reading
XML files inside your own code. It requires extension “svconnector”
which provides the base for all connector services.


.. _installation-update-600:

Updating to 6.0.0
^^^^^^^^^^^^^^^^^

Version 6.0.0 adds support for TYPO3 14 and PHP 8.5, while dropping support
for TYPO3 12 and PHP 8.1.

A new parameter :ref:`requestOptions <configuration-request-options>` is available.
It makes it possible to use any of the request options supported by Guzzle HTTP.
The "headers" parameter has been deprecated. Headers should be passed as part of the
"requestOptions" instead.

Encoding is now guessed from the declaration of the XML source, if defined.

Hooks have been entirely removed. Use only events.


.. _installation-update-500:

Updating to 5.0.0
^^^^^^^^^^^^^^^^^

Version 5.0.0 adds support for TYPO3 13 and PHP 8.4, while dropping support
for TYPO3 11 and PHP 7.4 and 8.0.

Events have been introduced to replace hooks. Existing hooks are still in place,
but are deprecated and events should now be used instead
(see the :ref:`svconnector manual <svconnector:developers-events>` for reference).

It is now possible to :ref:`choose another method <configuration-method>` that "GET"
when querying data, as well as :ref:`passing any number of headers <configuration-headers>` as array.


.. _installation-update-400:

Updating to 4.0.0
^^^^^^^^^^^^^^^^^

Version 4.0.0 adds support for TYPO3 12 and PHP 8.1, while dropping support
for TYPO3 10. It adapts to the new way of registering Connector Services.
The update process should be smooth with "svconnector" version 5.0.0.


.. _installation-update-300:

Updating to 3.0.0
^^^^^^^^^^^^^^^^^

Version 3.0.0 adds support for TYPO3 11 and PHP 8.0, while dropping support
for TYPO3 8 and 9. Apart from that it does not contain other changes and
the update process should be smooth.


.. _installation-update-240:

Updating to version 2.4.0
^^^^^^^^^^^^^^^^^^^^^^^^^

The "encoding" :ref:`configuration property <configuration>` has change behavior.
It used to accept all known encoding values plus all the synonyms defined in array
:php:`\TYPO3\CMS\Core\Charset\CharsetConverter::$synonyms`. This array does not
exist in TYPO3 v10 anymore, thus usage of synonyms has been dropped. Check
your configuration and verify that you use encoding names as defined in
https://www.php.net/manual/en/mbstring.supported-encodings.php.
