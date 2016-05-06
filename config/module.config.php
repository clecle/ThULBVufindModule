<?php
namespace ThULB\Module\Configuration;

return array (
    'vufind' => array (
        'plugin_managers' => array (
            'recorddriver' => array (
                'factories' => array (
                    'solrmarc' => 'ThULB\RecordDriver\Factory::getSolrMarc'
                ),
            ),
            'ils_driver' => array (
                'factories' => array (
                    'paia' => 'ThULB\ILS\Driver\Factory::getPAIA',
                    'paiapica' => 'ThULB\ILS\Driver\Factory::getPAIAPica',
                ),
            ),
        ),
    ),
);