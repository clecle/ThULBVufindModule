<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ThULB\View\Helper\Root;
use Zend\ServiceManager\ServiceManager;

/**
 * Description of Factory
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class Factory {
    /**
     * Construct the RecordLink helper.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return RecordLink
     */
    public static function getRecordLink(ServiceManager $sm)
    {
        return new RecordLink($sm->getServiceLocator()->get('VuFind\RecordRouter'));
    }
}
