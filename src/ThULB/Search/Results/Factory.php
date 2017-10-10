<?php

namespace ThULB\Search\Results;
use Zend\ServiceManager\ServiceManager;
use ThULB\Search\Summon\Results as SummonResults;

/**
 * Description of Factory
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class Factory {
    /**
     * Factory for Summon results object.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return Summon
     */
    public static function getSummon(ServiceManager $sm)
    {
        $options = $sm->getServiceLocator()
            ->get('VuFind\SearchParamsPluginManager')->get('Summon');
        $searchService = $sm->getServiceLocator()
            ->get('VuFind\Search');
        $recordLoader = $sm->getServiceLocator()
            ->get('VuFind\RecordLoader');
        
        // Clone the options instance in case caller modifies it:
        return new SummonResults(
                clone($options),
                $searchService,
                $recordLoader
            );
    }
}
