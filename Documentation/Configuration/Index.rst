.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: /Includes.rst.txt


.. _configuration:

Configuration
-------------

This chapter describes the parameters that can be used to configure the Feed/XML connector service.


.. _configuration-uri:

uri
^^^

Type
  string

Description
  URI of the XML file to read. This may be any of the following syntaxes:

  - absolute file path: :file:`/var/foo/web/fileadmin/import/bar.xml` (within the TYPO3 root path or :code:`TYPO3_CONF_VARS[BE][lockRootPath]`)
  - file path relative to the TYPO3 root: :file:`fileadmin/import/foo.xml`
  - file path using :code:`EXT:`: :file:`EXT:foo/Resources/Private/Data/bar.xml`
  - fully qualified URL, e.g. :file:`http://www.example.com/foo.xml`
  - FAL reference with storage ID and file identifier: :file:`FAL:2:/foo.xml`
  - custom syntax: :file:`MYKEY:whatever_you_want`, see :ref:`Connector Services <svconnector:developers-utilities-reading-files>`


.. _configuration-method:

method
^^^^^^

Type
  string

Description
  Method used to get the file (GET, POST, or whatever else is relevant).
  This parameter is optional and the method defaults to GET.


.. _configuration-request-options:

requestOptions
^^^^^^^^^^^^^^

Type
  array

Description
  Key-value pairs of options that can be passed to the request. Any of the
  `request options supported by Guzzle HTTP <https://docs.guzzlephp.org/en/stable/request-options.html>`_
  may be used.

  .. note::

     This makes sense only when using fully qualified URLs in the :ref:`uir parameter <configuration-uri>`.

Example
  Passing a "page" information in the body and setting an accepted mime type in the headers.

  .. code-block:: php

      'requestOptions' => [
         'body' => '{"page": 1}',
         'headers' => [
            'Accept' => 'application/xml',
         ],
      ],


.. _configuration-headers:

headers
^^^^^^^

Type
  array

Description
  Key-value pairs of headers that should be sent along with the request.

  .. warning::

     This parameter has been deprecated. It will be removed in the next major version.
     Use :ref:`requestOptions <configuration-request-options>` instead.

Example
  Example headers for setting an alternate user agent and defining what reponse
  format to accept.

  .. code-block:: php

      'headers' => [
         'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:75.0) Gecko/20100101 Firefox/75.0',
         'Accept' => 'application/xml',
      ]


.. _configuration-encoding:

encoding
^^^^^^^^

Type
  string

Description
  Encoding of the data found in the file. This value must match any of
  the encoding values recognized by the PHP libray "mbstring". See
  https://www.php.net/manual/en/mbstring.supported-encodings.php

  .. warning::

     If your are aiming for the array format (i.e. calling
     :code:`fetchArray()`), you should not define this property. Indeed the
     :code:`\Cobweb\Svconnector\Utility\ConnectorUtility::convertXmlToArray()`
     which is used in this case relies on the SimpleXML library, which
     already takes care of the encoding conversion. To avoid a double
     encoding just ignore this property.
