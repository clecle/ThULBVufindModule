<?php

namespace ThULB\ILS\Driver;
use Zend\ServiceManager\ServiceManager;

class Factory
{
    public static function getPAIA(ServiceManager $sm)
    {
        return new PAIA(
            $sm->getServiceLocator()->get('VuFind\DateConverter'),
            $sm->getServiceLocator()->get('VuFind\SessionManager'),
            $sm->getServiceLocator()->get('VuFind\RecordLoader')
        );
    }
}