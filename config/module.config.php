<?php
namespace ThULB\Module\Configuration;

return array (
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
            ),
        ),
    ),
  'view_helpers' => array(
      'invokables' => array(
        'thulb_metadatahelper' => 'ThULB\View\Helper\Record\MetaDataHelper',
        'thulb_holdinghelper' => 'ThULB\View\Helper\Record\HoldingHelper'
      ),
   ),
);