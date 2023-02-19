.. include:: ../../Includes.txt

.. _changingTheDefaultTemplateForTheViews:

===========================================
Changing the Default Template for the Views
===========================================

The extension `sav_library_mvc
<https://extensions.typo3.org/extension/sav_library_mvc>`_ 
comes with default templates for
the views. Templates, layouts, partials are respectively in the
directory ``Resources/Private/Templates/Default``,
``Resources/Private/Layouts`` and ``Resources/Private/Partials``. 
They can be changed in TypoScript as follows:

::

   plugin.tx_yourExtensionNameWithoutUnderscores.templateRootPath = yourTemplateRootPath
   plugin.tx_yourExtensionNameWithoutUnderscores.layoutRootPath = yourLayoutRootPath
   plugin.tx_yourExtensionNameWithoutUnderscores.partialRootPath = yourPartialRootPath

