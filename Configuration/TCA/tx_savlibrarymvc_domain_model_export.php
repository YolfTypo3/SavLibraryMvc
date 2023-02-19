<?php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:test/Resources/Private/Language/locallang_db.xlf:tx_test_domain_model_aaa',
        'label' => 'r',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:test/Resources/Public/Icons/tx_savlibrarymvc_domain_model_export.gif'
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label'  => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf.xlf:LGL.hidden',
            'config' => [
                'type'  => 'check',
                'default' => 0,
            ]
        ],
        'cid' => [
            'exclude' => 1,
            'label'  => 'LLL:EXT:sav_library_mvc/Resources/Private/Language/locallang_db.xlf:tx_savlibrarymvc_domain_model_export.cid',
            'config' => [
                'type' => 'input',
                'size' => '7',
                'eval' => 'int'
            ],
        ],
        'name' => [
            'exclude' => 1,
            'label'  => 'LLL:EXT:sav_library_mvc/Resources/Private/Language/locallang_db.xlf:tx_savlibrarymvc_domain_model_export.name',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim'
            ],
        ],
        'template_file' => [
            'exclude' => 1,
            'label'  => 'LLL:EXT:sav_library_mvc/Resources/Private/Language/locallang_db.xlf:tx_savlibrarymvc_domain_model_export.templateFile',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim'
            ],
        ],
        'variables' => [
            'exclude' => 1,
            'label'  => 'LLL:EXT:sav_library_mvc/Resources/Private/Language/locallang_db.xlf:tx_savlibrarymvc_domain_model_export.variables',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5,
            ],
        ],
        'xslt_file' => [
            'exclude' => 1,
            'label'  => 'LLL:EXT:sav_library_mvc/Resources/Private/Language/locallang_db.xlf:tx_savlibrarymvc_domain_model_export.xsltFile',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim'
            ],
        ],
        'exec' => [
            'exclude' => 1,
            'label'  => 'LLL:EXT:sav_library_mvc/Resources/Private/Language/locallang_db.xlf:tx_savlibrarymvc_domain_model_export.exec',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim'
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => 'hidden, cid, name, template_file, variables, xslt_file, exec',
        ],
    ],
    'palettes' => [
        '1' => ['showitem' => '']
    ],
];
