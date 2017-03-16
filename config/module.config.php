<?php
namespace ThULB\Module\Configuration;

return [
    'controllers' => [
        'factories' => [
            'record' => 'ThULB\Controller\Factory::getRecordController',
        ],
        'invokables'    => [
            'summon' => 'ThULB\Controller\SummonController',
            'summonrecord' => 'ThULB\Controller\SummonrecordController',
            'ajax' => 'ThULB\Controller\AjaxController',
        ]
    ],
    'vufind' => [
        'plugin_managers' => [
            'hierarchy_treedataformatter' => [
                'invokables' => [
                    'json' => 'ThULB\Hierarchy\TreeDataFormatter\Json'
                ],
            ],
            'hierarchy_treedatasource' => [
                'factories' => [
                    'solr' => 'ThULB\Hierarchy\TreeDataSource\Factory::getSolr',
                ]
            ],
            'ils_driver' => [
                'factories' => [
                    'paia' => 'ThULB\ILS\Driver\Factory::getPAIA'
                ]
            ],
            'recorddriver' => [
                'factories' => [
                    'solrmarc' => 'ThULB\RecordDriver\Factory::getSolrMarc',
                    'summon' => 'ThULB\RecordDriver\Factory::getSummon'
                ],
            ],
            'recordtab' => [
                'factories' => [
                    'articlecl' => 'ThULB\RecordTab\Factory::getArticleCollectionList',
                    'nonarticlecl' => 'ThULB\RecordTab\Factory::getNonArticleCollectionList'
                ]
            ]
        ],
        'recorddriver_tabs' => [
            'VuFind\RecordDriver\SolrDefault' => [
                'tabs' => [
                    'Similar' => null,
                    'ArticleCollectionList' => 'articlecl',
                    'NonArticleCollectionList' => 'nonarticlecl',
                    'Description'   => null,
                    'Reviews' => null,
                    'Excerpt' => null
                ],
                'backgroundLoadedTabs' => ['ArticleCollectionList', 'NonArticleCollectionList']
            ],
            'VuFind\RecordDriver\SolrMarc' => [
                'tabs' => [
                    'Similar' => null,
                    'ArticleCollectionList' => 'articlecl',
                    'NonArticleCollectionList' => 'nonarticlecl',
                    'Description'   => null,
                    'Reviews' => null,
                    'Excerpt' => null
                ],
                'backgroundLoadedTabs' => ['ArticleCollectionList', 'NonArticleCollectionList']
            ],
            'VuFind\RecordDriver\Summon' => [
                'tabs' => [
                    'Description' => null,
                    'Reviews' => null,
                    'Excerpt' => null
                ],
                'defaultTab' => null
            ]
        ]
    ],
   'view_helpers' => [
       'invokables' => [
         'thulb_metadatahelper' => 'ThULB\View\Helper\Record\MetaDataHelper',
         'thulb_holdinghelper' => 'ThULB\View\Helper\Record\HoldingHelper'
       ],
    ],
    
    // Authorization configuration:
    'zfc_rbac' => [
        'vufind_permission_provider_manager' => [
            'factories' => [
                'queriedCookie' => 'ThULB\Role\PermissionProvider\Factory::getQueriedCookie',
            ]
        ],
    ],
];
