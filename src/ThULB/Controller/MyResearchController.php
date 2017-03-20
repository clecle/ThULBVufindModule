<?php

namespace ThULB\Controller;
use VuFind\Controller\MyResearchController as OriginalController;


/**
 * Description of MyResearchController
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class MyResearchController extends OriginalController
{
    const ID_URI_PREFIX = 'http://uri.gbv.de/document/opac-de-27:ppn:';
    
    /**
     * Get a record driver object corresponding to an array returned by an ILS
     * driver's getMyHolds / getMyTransactions method.
     *
     * @param array $current Record information
     *
     * @return \VuFind\RecordDriver\AbstractBase
     */
    protected function getDriverForILSRecord($current)
    {
        $current['id'] = str_replace(self::ID_URI_PREFIX, '', $current['id']);
        
        return parent::getDriverForILSRecord($current);
    }
}
