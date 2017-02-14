<?php
namespace ThULB\Module\Configuration;

return [
    'controllers' => [
        'invokables'    => [
            'summon' => 'ThULB\Controller\SummonController',
            'summonrecord' => 'ThULB\Controller\SummonrecordController'
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
            ]
        ],
        'recorddriver_tabs' => [
            'VuFind\RecordDriver\SolrDefault' => [
                'tabs' => [
                    'Similar' => null,
                    'CollectionList' => 'CollectionList',
                    'Description'   => null
                ]
            ],
            'VuFind\RecordDriver\SolrMarc' => [
                'tabs' => [
                    'Similar' => null,
                    'CollectionList' => 'CollectionList',
                    'Description'   => null
                ]
            ],
            'VuFind\RecordDriver\Summon' => [
                'tabs' => [
                    'Description' => null
                ],
                'defaultTab' => null,
            ]
        ]
    ],
  'view_helpers' => [
      'invokables' => [
        'thulb_metadatahelper' => 'ThULB\View\Helper\Record\MetaDataHelper',
        'thulb_holdinghelper' => 'ThULB\View\Helper\Record\HoldingHelper'
      ],
   ],
];
