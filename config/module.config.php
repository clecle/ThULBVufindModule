<?php
namespace ThULB\Module\Configuration;

return array (
    'controllers' => array (
        'invokables'    => array (
            'summon' => 'ThULB\Controller\SummonController',
            'summonrecord' => 'ThULB\Controller\SummonrecordController'
        )
    ),
    'vufind' => array (
        'plugin_managers' => array (
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
            'recorddriver' => array (
                'factories' => array (
                    'solrmarc' => 'ThULB\RecordDriver\Factory::getSolrMarc',
                    'summon' => 'ThULB\RecordDriver\Factory::getSummon'
                ),
            )
        ),
        'recorddriver_tabs' => array (
            'VuFind\RecordDriver\SolrDefault' => array (
                'tabs' => array (
                    'Similar' => null,
                    'CollectionList' => 'CollectionList',
                )
            ),
            'VuFind\RecordDriver\SolrMarc' => array (
                'tabs' => array (
                    'Similar' => null,
                    'CollectionList' => 'CollectionList'
                )
            )
        )
    ),
  'view_helpers' => array(
      'invokables' => array(
        'thulb_metadatahelper' => 'ThULB\View\Helper\Record\MetaDataHelper',
        'thulb_holdinghelper' => 'ThULB\View\Helper\Record\HoldingHelper'
      ),
   ),
);