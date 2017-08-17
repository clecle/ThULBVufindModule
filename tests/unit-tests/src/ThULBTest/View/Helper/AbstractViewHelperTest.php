<?php

namespace ThULBTest\View\Helper;

use ThULB\RecordDriver\SolrVZGRecord;
use VuFind\I18n\Translator\Loader\ExtendedIni;
use VuFind\Service\Factory as ServiceFactory;
use Zend\Config\Config;
use Zend\Http\Client;
use Zend\I18n\Translator\Translator;
use Zend\Config\Reader\Ini as IniReader;
use Zend\Mvc\I18n\Translator as MvcTranslator;

/**
 * General view helper test class that provides usually used operations.
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
abstract class AbstractViewHelperTest extends \VuFindTest\Unit\ViewHelperTestCase
{
    const FINDEX_REQUEST_PATH = '/index/31/GBV_ILN_31/select';
    const FINDEX_QUERY_STRING = '?wt=json&fq=collection_details:"GBV_ILN_31"+AND+collection_details:"GBV_GVK"&q=id:';
    
    protected $translationLocale = 'de';
    
    protected $config;
    
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
     * Query for a record in the index.
     * 
     * @param string $ppn Pica production number of a record
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
        $marcObject = new SolrVZGRecord($this->getMainConfig());
        
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

    /**
     * Get view helpers needed by test.
     *
     * @return array
     */
    protected function getViewHelpers()
    {   
        $context = new \VuFind\View\Helper\Root\Context();
        $helpers = [
//            'auth' => new \VuFind\View\Helper\Root\Auth($this->getMockBuilder('VuFind\Auth\Manager')->disableOriginalConstructor()->getMock()),
            'context' => $context,
            'openUrl' => new \VuFind\View\Helper\Root\OpenUrl($context, []),
            'proxyUrl' => new \VuFind\View\Helper\Root\ProxyUrl(),
            'record' => new \VuFind\View\Helper\Root\Record(),
            'recordLink' => new \ThULB\View\Helper\Root\RecordLink($this->getMockBuilder('VuFind\Record\Router')->disableOriginalConstructor()->getMock()),
            'searchTabs' => $this->getMockBuilder('VuFind\View\Helper\Root\SearchTabs')->disableOriginalConstructor()->getMock(),
            'translate' => new \VuFind\View\Helper\Root\Translate(),
            'transEsc' => new \VuFind\View\Helper\Root\TransEsc(),
//            'usertags' => new \VuFind\View\Helper\Root\UserTags(),
        ];
        $helpers['translate']->setTranslator($this->getTranslator());
        
        return $helpers;
    }
    
    /**
     * Factory for a valid Translator
     */
    protected function getTranslator() {
        $translator = new MvcTranslator(new Translator());
        
        $pathStack = [
            APPLICATION_PATH . '/languages',
            LOCAL_OVERRIDE_DIR . '/languages'
        ];
        $fallbackLocales = ['de', 'en'];
        
        $translator->getPluginManager()->setService('ExtendedIni',
                new \VuFind\I18n\Translator\Loader\ExtendedIni(
                    $pathStack, $fallbackLocales
                )
            );
        
        $translator->setLocale($this->translationLocale)
                ->addTranslationFile('ExtendedIni', null, 'default', $this->translationLocale)
                ->addTranslationFile('ExtendedIni', 'Languages', 'Languages', $this->translationLocale)
                ->addTranslationFile('ExtendedIni', 'CreatorRoles', 'CreatorRoles', $this->translationLocale);
        
        return $translator;
    }
    
    /**
     * Define a locale (e.g. 'de' or 'en')
     * 
     * @param string $locale
     */
    protected function setTranslationLocale($locale) {
        $this->translationLocale = $locale;
    }
    
    protected function getMainConfig()
    {
        if (is_null($this->config)) {
            $iniReader = new IniReader();
            $this->config = new Config($iniReader->fromFile(THULB_CONFIG_FILE), true);
        }
        
        return $this->config;
    }
}
