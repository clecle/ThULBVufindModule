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
        ),
    ),
);