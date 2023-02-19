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
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace YolfTypo3\SavLibraryMvc\ViewConfiguration;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Controller\FlashMessages;
use YolfTypo3\SavLibraryMvc\Domain\Model\Export;

/**
 * Export view configuration for the SAV Library Mvc
 */
class ExportViewConfiguration extends AbstractViewConfiguration
{
    /**
     * Gets the view configuration
     *
     * @param array $arguments
     *            Arguments from the action
     * @return array The view configuration
     */
    public function getConfiguration(array $arguments): array
    {
        // Gets the special parameters from arguments, uncompresses it and modifies it if needed
        $special = $arguments['special'];
        $uncompressedParameters = AbstractController::uncompressParameters($special);
        $special = AbstractController::compressParameters($uncompressedParameters);

        $exportIsLoaded = isset($uncompressedParameters['exportUid']);

        // Gets the content Id
        $contentId = $this->controller->getContentObjectRenderer()->data['uid'];

        // Gets the main repository
        $exportRepository = $this->controller->getExportRepository();

        // Gets the configuration
        if ($exportIsLoaded) {
            $exportUid = $uncompressedParameters['exportUid'];
            $exportConfiguration = $exportRepository->findByUid($exportUid);
        } else {
            $exportConfiguration = GeneralUtility::makeInstance(Export::class);
        }

        // Checks if the execution must be processed
        $userIsAllowedToExportData = $this->controller->getFrontendUserManager()
        ->userIsAllowedToExportData();
        if ($arguments['executeRequested'] && $userIsAllowedToExportData) {
            $this->executeExport($exportConfiguration);
        }

        // Gets the options
        $query = $exportRepository->createQuery();
        $options = $query->matching($query->equals('cid', $contentId))->execute();

        // Sets general configuration values
        $this->addGeneralViewConfiguration('extensionKey', $this->controller->getControllerExtensionKey());
        $this->addGeneralViewConfiguration('controllerName', $this->controller->getControllerName());
        $this->addGeneralViewConfiguration('special', $special);
        $this->addGeneralViewConfiguration('userIsAllowedToExportData', $userIsAllowedToExportData);
        $this->addGeneralViewConfiguration('object', $exportConfiguration);
        $this->addGeneralViewConfiguration('options', $options);
        $this->addGeneralViewConfiguration('cid', $contentId);
        $this->addGeneralViewConfiguration('exportIsLoaded', $exportIsLoaded);
        $this->addGeneralViewConfiguration('exportUid', $exportUid);

        // Sets the view configuration
        $viewConfiguration = [
            'general' => $this->getGeneralViewConfiguration(),
        ];

        return $viewConfiguration;
    }

    /**
     * Execute the export
     *
     * @param Export $exportConfiguration
     * @return void
     */
    protected function executeExport(Export $exportConfiguration)
    {
        // Gets the template
        $templateFileName = GeneralUtility::getFileAbsFileName($exportConfiguration->getTemplateFile());
        $template = GeneralUtility::getUrl($templateFileName);
        if ($template === false) {
            FlashMessages::addError('error.unknownTemplateFile', [$templateFileName]);
            return;
        }

        // Parses the template
        try {
            $variables = Yaml::parse($exportConfiguration->getVariables());
        } catch (ParseException $e) {
            FlashMessages::addError('error.failureInYamlExportVariables');
            return;
        }
        if (is_array($variables)) {
            $parsedTemplate = $this->templateParser->parse($template, $variables);
        } else {
            $parsedTemplate = $this->templateParser->parse($template);
        }

        $temporaryFileName = Environment::getPublicPath() . '/typo3temp/' . $this->controller->getControllerExtensionKey() . '/' . basename($templateFileName);
        $result = GeneralUtility::writeFileToTypo3tempDir($temporaryFileName, $parsedTemplate);
        if ($result !== null) {
            FlashMessages::addError('error.failureInWritingTemporaryFile', [$result]);
            return;
        }

        // Processes the xslt
        $xsltFile = $exportConfiguration->getXsltFile();
        if (! empty($xsltFile)) {
            if ($this->processXsltFile($temporaryFileName, $xsltFile) === false) {
                return;
            }
        }

        // Processes the exec command
        $exec = $exportConfiguration->getExec();
        if (! empty($exec)) {
            if ($this->processExec($temporaryFileName, $exec) != 0) {
                FlashMessages::addError('error.failureInExec');
                return;
            }
        }

        // Deletes the temporary file
        if (file_exists($temporaryFileName)) {
            unlink($temporaryFileName);
        }
    }

    /**
     * Processes the xslt file
     *
     * @param string $xmlFileToProcess
     * @param string $xmlFileToProcess
     * @return bool Returns false if an error occured, true otherwise
     */
    protected function processXsltFile(string $xmlFileToProcess, string $xsltFile): bool
    {
        if (file_exists($xsltFile)) {

            // Loads the XML source
            $xml = new \DOMDocument();
            libxml_use_internal_errors(true);
            $typoScriptConfiguration = [];
            if (@$xml->load($xmlFileToProcess) === false) {

                $typoScriptConfiguration['parameter'] = $xmlFileToProcess;
                $typoScriptConfiguration['target'] = '_blank';
                FlashMessages::addError('error.incorrectXmlProducedFile', [
                    $this->controller->getContentObjectRenderer()->typoLink(FlashMessages::translate('error.xmlErrorFile'), $typoScriptConfiguration)
                ]);

                // Gets the errors
                $errors = libxml_get_errors();
                foreach ($errors as $error) {
                     FlashMessages::addError('error.xmlError', [
                         $error->message,
                         $error->line
                     ]);
                }
                libxml_clear_errors();
                return false;
            }

            // Loads the xslt file
            $xsl = new \DOMDocument();
            if (@$xsl->load($xsltFile) === false) {
                FlashMessages::addError('error.incorrectXsltFile', [
                    $xsltFile
                ]);
                return false;
            }

            // Configures the transformer
            $proc = new \XSLTProcessor();
            $proc->importStyleSheet($xsl); // attach the xsl rules

            // Writes the result directly
            $transformedContent = @$proc->transformToXml($xml);
            if ($transformedContent === false) {
                FlashMessages::addError('error.incorrectXsltResult');
                return false;
            }

            GeneralUtility::writeFile($xmlFileToProcess, $transformedContent);
            return true;
        } else {
            FlashMessages::addError('error.fileDoesNotExist', [
                $xsltFile
            ]);
            return false;
        }
    }

    /**
     * Processes the exec command
     *
     * @param string $fileName
     * @param string $exec
     *
     * @return int returns 0 if exec was correctly processed
     */
    protected function processExec(string $fileName, string $exec): int
    {
        // Processes special controls
        $match = [];
        if (preg_match('/^(RENAME|COPY)\s+(###FILE###)\s+(.*)$/', $exec, $match)) {
            switch ($match[1]) {
                case 'RENAME':
                    $return = @rename($fileName, Environment::getPublicPath() . '/' . $match[3]);
                    break;
                case 'COPY':
                    $return = @copy($fileName, Environment::getPublicPath() . '/' . $match[3]);
                    break;
            }
            return $return ? 0 : 1;
        }
        if (! $this->controller->getSetting('allowExec')) {
            FlashMessages::addError('error.notAllowedToUseComplexExecCommand');
            return 0;
        }

        // Replaces some tags
        $cmd = str_replace('###FILE###', $fileName, $exec);
        $cmd = str_replace('###SITEPATH###', Environment::getPublicPath(), $cmd);

        // Processes the command if not in safe mode
        if (! ini_get('safe_mode')) {
            $cmd = escapeshellcmd($cmd);
        }

        // Special processing for white spaces in windows directories
        $cmd = preg_replace('/\/(\w+(?:\s+\w+)+)/', '/"$1"', $cmd);

        // Executes the command
        CommandUtility::exec($cmd, $_, $returnValue);

        return (int) $returnValue;
    }
}
