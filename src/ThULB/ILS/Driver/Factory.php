<?php

namespace ThULB\ILS\Driver;
use Zend\ServiceManager\ServiceManager;

class Factory
{
    public static function getDAIA(ServiceManager $sm)
    {
        $daia = new DAIA(
            $sm->getServiceLocator()->get('VuFind\DateConverter')
        );
        $daia->setCacheStorage(
            $sm->getServiceLocator()->get('VuFind\CacheManager')->getCache('object')
        );
        return $daia;
    }
    
    public static function getPAIA(ServiceManager $sm)
    {
        return new PAIA(
            $sm->getServiceLocator()->get('VuFind\DateConverter'),
            $sm->getServiceLocator()->get('VuFind\SessionManager')
        );
    }
}