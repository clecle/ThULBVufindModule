<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ThULBTest\View\Helper;

use ThULB\RecordDriver\SolrVZGRecord;
use Zend\Http\Client;

/**
 * Description of AbstractViewHelperTest
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
abstract class AbstractViewHelperTest extends \VuFindTest\Unit\ViewHelperTestCase
{
    const FINDEX_REQUEST_PATH = '/index/31/GBV_ILN_31/select';
    const FINDEX_QUERY_STRING = '?wt=json&fq=collection_details:"GBV_ILN_31"+AND+collection_details:"GBV_GVK"&q=id:';
    
    /**
     * Get a working renderer.
     *
     * @param array  $plugins Custom VuFind plug-ins to register
     * @param string $theme   Theme directory to load from
     *
     * @return \Zend\View\Renderer\PhpRenderer
     */
    protected function getPhpRenderer($plugins = [], $theme = 'thulb')
    {
        $resolver = new \Zend\View\Resolver\TemplatePathStack();

        $resolver->setPaths(
            [
                $this->getPathForTheme('root'),
                $this->getPathForTheme('bootstrap3'),
                $this->getPathForTheme($theme)
            ]
        );
        $renderer = new \Zend\View\Renderer\PhpRenderer();
        $renderer->setResolver($resolver);
        if (!empty($plugins)) {
            $pluginManager = $renderer->getHelperPluginManager();
            foreach ($plugins as $key => $value) {
                $pluginManager->setService($key, $value);
            }
        }
        return $renderer;
    }
    
     /**
     * @param $ppn
     * @return SolrVZGRecord|null
     * @throws \HttpException
     */
    protected function getRecordFromFindex($ppn)
    {
        $url = FINDEX_TEST_HOST . self::FINDEX_REQUEST_PATH . self::FINDEX_QUERY_STRING . $ppn;
        $client = new Client($url, array(
            'maxredirects' => 3,
            'timeout' => 10
        ));
        $response = $client->send();
        if ($response->getStatusCode() > 299) {
            throw new \HttpException("Status code " . $response->getStatusCode() . " for $url.");
        }
        $jsonString = trim($response->getBody());
        $jsonObject = json_decode($jsonString, true);
        $marcObject = new SolrVZGRecord();
        if ($jsonObject['response']['numFound'] < 1) {
            $this->markTestIncomplete("No document found with ppn \"$ppn\"...");
        }
        try {
            $marcObject->setRawData($jsonObject['response']['docs'][0]);
        } catch (\File_MARC_Exception $e) {
            echo "Record $ppn: " . $e->getMessage() . "\n";
            return null;
        }
        return $marcObject;
    }
}
