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
            'ils_driver' => [
                'factories' => [
                    'paia' => 'ThULB\ILS\Driver\Factory::getPAIA'
                ]
            ],
            'recorddriver' => array (
                'factories' => array (
                    'solrmarc' => 'ThULB\RecordDriver\Factory::getSolrMarc'
                ),
            )
        ),
        'recorddriver_tabs' => array (
            'VuFind\RecordDriver\SolrDefault' => array (
                'tabs' => array (
                    'Similar' => null,
                )
            ),
            'VuFind\RecordDriver\SolrMarc' => array (
                'tabs' => array (
                    'Similar' => null,
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