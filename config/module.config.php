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
);