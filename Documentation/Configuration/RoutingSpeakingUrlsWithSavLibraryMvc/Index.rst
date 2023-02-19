.. include:: ../../Includes.txt

.. _routingSpeakingUrlsWithSavLibraryMvc:

===============================================
Routing - "Speaking URLs" With SAV Library Mvc
===============================================

The `SAV Library Kickstarter
<https://extensions.typo3.org/extension/sav_library_kickstarter>`_ 
generated the configuration for Speaking URLs in the
file ``Configuration/Routes/Default.yaml`` of the extension.

Import this file in the ``config.yaml`` file of your site using the ``imports`` instruction.

::

    imports:
      - resource: 'EXT:sav_librarymvc_example0/Configuration/Routes/Default.yaml' 


.. hint::

    See the `Routing - "Speaking URLs" in TYPO3 
    <https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/Routing/Index.html>`_
    section of the Main TYPO3 Core documentation for details.

The following configuration illustrates the configuration for the
`sav_librarymvc_example0
<https://extensions.typo3.org/extension/sav_librarymvc_example0>`_ extension. 

::

    routeEnhancers:
      SavLibrarymvcExample0:
        type: Extbase
        extension: SavLibrarymvcExample0
        plugin: Default
        routes:
          - routePath: '/savlibrarymvcexample0/test'
            _controller: 'Test::list'
          - routePath: '/savlibrarymvcexample0/test/{special}'
            _controller: 'Test::list'
          - routePath: '/savlibrarymvcexample0/test/single/{special}'
            _controller: 'Test::single'
          - routePath: '/savlibrarymvcexample0/test/edit/{special}'
            _controller: 'Test::edit'
          - routePath: '/savlibrarymvcexample0/test/delete/{special}'
            _controller: 'Test::delete'
          - routePath: '/savlibrarymvcexample0/test/subform/delete/{special}'
            _controller: 'Test::deleteInSubform'
          - routePath: '/savlibrarymvcexample0/test/subform/up/{special}'
            _controller: 'Test::upInSubform'
          - routePath: '/savlibrarymvcexample0/test/subform/down/{special}'
            _controller: 'Test::downInSubform'
          - routePath: '/savlibrarymvcexample0/test/file/delete/{special}'
            _controller: 'Test::deleteFile'
        requirements:
          special: '[a-z0-9\-]+'
