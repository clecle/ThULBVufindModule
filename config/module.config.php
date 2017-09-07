<?php
namespace ThULB\Module\Configuration;

$config = [
    'controllers' => [
        'invokables'    => [
            'ajax' => 'ThULB\Controller\AjaxController',
            'my-research' => 'ThULB\Controller\MyResearchController',
            'summon' => 'ThULB\Controller\SummonController',
            'summonrecord' => 'ThULB\Controller\SummonrecordController',
        ]
    ],
    'vufind' => [
        'plugin_managers' => [
            'db_table' => [
                'factories' => [
                    'user' => 'ThULB\Db\Table\Factory::getUser'
                ],
            ],
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
                ],
                'invokables' => [
                    'staffviewcombined' => 'ThULB\RecordTab\StaffViewCombined'
                ]
            ],
            'search_results' => [
                'factories' => [
                    'summon' => 'ThULB\Search\Results\Factory::getSummon'
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
                    'Excerpt' => null,
                    'Details' => 'staffviewcombined'
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

$routeGenerator = new \VuFind\Route\RouteGenerator();
$routeGenerator->addStaticRoute($config, 'MyResearch/ChangePasswordLink');

return $config;