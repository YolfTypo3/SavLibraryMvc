<?php
namespace SAV\SavLibraryMvc\Form\Resolver;

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
use TYPO3\CMS\Backend\Form\NodeResolverInterface;
use TYPO3\CMS\Rtehtmlarea\Form\Element\RichTextElement;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Form\NodeFactory;

/**
 * This resolver will return the RichTextElement render class of ext:rtehtmlarea if RTE is enabled for this field.
 */
class RichTextNodeResolver implements NodeResolverInterface
{

    /**
     * Global options from NodeFactory
     *
     * @var array
     */
    protected $data;

    /**
     * Default constructor receives full data array
     *
     * @param NodeFactory $nodeFactory
     * @param array $data
     */
    public function __construct(NodeFactory $nodeFactory, array $data)
    {
        $this->data = $data;
    }

    /**
     * Returns RichTextElement as class name if RTE widget should be rendered.
     *
     * @return string|void New class name or void if this resolver does not change current class name.
     */
    public function resolve()
    {
        $table = $this->data['tableName'];
        $fieldName = $this->data['fieldName'];
        $row = $this->data['databaseRow'];
        $parameterArray = $this->data['parameterArray'];

        // @todo: Most of this stuff is prepared by data providers within $this->data already
        $specialConfiguration = BackendUtility::getSpecConfParts($parameterArray['fieldConf']['defaultExtras']);

        // If "richtext" is within defaultExtras
        if (isset($specialConfiguration['richtext'])) {
            $rteSetupConfiguration = BackendUtility::RTEsetup($rteSetup['properties'], $table, $fieldName, $rteTcaTypeValue);
            if (! $rteSetupConfiguration['disabled']) {
                // Finally, we're sure the editor should really be rendered ...
                return \SAV\SavLibraryMvc\Form\Element\RichTextElement::class;
            }
        }
        return null;
    }
}
