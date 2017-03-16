<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ThULB\Controller;
use VuFind\Controller\RecordController as OriginalRecordController;

/**
 * Description of RecordController
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class RecordController extends OriginalRecordController
{
    protected function loadTabDetails() {
        parent::loadTabDetails();
        
        // if a tab is called directly, then don't load it in the background
        $request = $this->getRequest();
        $uriParts = explode('/', $request->getRequestUri());
        $this->backgroundTabs = array_diff($this->backgroundTabs, [end($uriParts)]);
    }
}
