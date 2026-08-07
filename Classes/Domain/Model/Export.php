<?php

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

namespace YolfTypo3\SavLibraryMvc\Domain\Model;

/**
 * Export Model for the SAV Library MVC
 */
class Export extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * The name variable.
     *
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("String")
     */
    protected string $name;

    /**
     * The content id
     *
     * @var int
     */
    protected int $cid;

    /**
     * The templateFile variable.
     *
     * @var string
     */
    protected string $templateFile;

    /**
     * The variables variable.
     *
     * @var string
     */
    protected string $variables;

    /**
     * The xsltFile variable.
     *
     * @var string
     */
    protected string $xsltFile;

    /**
     * The exec variable.
     *
     * @var string
     */
    protected string $exec;

    /**
     * Getter for name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Setter for name
     *
     * @param string $name
     * 
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name =  $name;
    }


    /**
     * Getter for cid
     *
     * @return int
     */
    public function getCid(): int
    {
        return $this->cid;
    }

    /**
     * Setter for cid
     *
     * @param int $cid
     * 
     * @return void
     */
    public function setCid(int $cid): void
    {
        $this->cid =  $cid;
    }

    /**
     * Getter for templateFile
     *
     * @return string
     */
    public function getTemplateFile(): string
    {
        return $this->templateFile;
    }

    /**
     * Setter for templateFile
     *
     * @param string $templateFile
     * 
     * @return void
     */
    public function setTemplateFile(string $templateFile): void
    {
        $this->templateFile =  $templateFile;
    }

    /**
     * Getter for variables
     *
     * @return string
     */
    public function getVariables(): string
    {
        return $this->variables;
    }

    /**
     * Setter for variables
     *
     * @param string $variables
     * 
     * @return void
     */
    public function setVariables(string $variables): void
    {
        $this->variables =  $variables;
    }

    /**
     * Getter for xsltFile
     *
     * @return string
     */
    public function getXsltFile(): string
    {
        return $this->xsltFile;
    }

    /**
     * Setter for xsltFile
     *
     * @param string $xsltFile
     * 
     * @return void
     */
    public function setXsltFile(string $xsltFile): void
    {
        $this->xsltFile =  $xsltFile;
    }

    /**
     * Getter for exec
     *
     * @return string
     */
    public function getExec(): string
    {
        return $this->exec;
    }

    /**
     * Setter for exec
     *
     * @param string $exec
     * 
     * @return void
     */
    public function setExec(string $exec): void
    {
        $this->exec =  $exec;
    }
}
