<?php

return array(
    'vufind' => [
        'plugin_managers' => [
            'ils_driver' => [
                'factories' => [
                    'paia' => 'ThULB\ILS\Driver\Factory::getPAIA'
                ]
            ],
            'recorddriver' => [
                'factories' => [
                    'solrmarc' => 'ThULB\RecordDriver\Factory::getSolrMarc'
                ]
            ]
        ]
    ]
);