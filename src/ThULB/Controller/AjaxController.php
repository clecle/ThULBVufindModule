<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ThULB\Controller;

use VuFind\Controller\AjaxController as OriginalAjaxController;

/**
 * Description of AjaxController
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class AjaxController extends OriginalAjaxController {
    
    public function getResultCountAjax()
    {
        $index = $this->params()->fromPost('index', $this->params()->fromQuery('index'));
        $lookFor = $this->params()->fromPost('lookfor', $this->params()->fromQuery('lookfor'));
        $type = $this->params()->fromPost('type', $this->params()->fromQuery('type'));
       
        $runner = $this->getServiceLocator()->get('VuFind\SearchRunner');
        $result = $runner->run(['limit' => '0', 'type' => $type, 'lookfor' => $lookFor], $index);
        
        $numberFormatter = $this->getViewRenderer()->plugin('localizedNumber');
        
        return $this->output(['count' => $numberFormatter($result->getResultTotal())], self::STATUS_OK);
    }
    
    /**
     * Support method for getItemStatuses() -- process a single bibliographic record
     * for location settings other than "group".
     *
     * @param array  $record            Information on items linked to a single bib
     *                                  record
     * @param array  $messages          Custom status HTML
     *                                  (keys = available/unavailable)
     * @param string $locationSetting   The location mode setting used for
     *                                  pickValue()
     * @param string $callnumberSetting The callnumber mode setting used for
     *                                  pickValue()
     *
     * @return array                    Summarized availability information
     */
    protected function getItemStatus($record, $messages, $locationSetting,
        $callnumberSetting
    ) {
        $result = parent::getItemStatus($record, $messages, $locationSetting, $callnumberSetting);
        
        // overwrite the detailled service information to only show basic availability
        $availabilityMap = ['true' => 'available', 'false' => 'unavailable'];
        $result['availability_message'] = $messages[$availabilityMap[$result['availability']]];
        
        return $result;
    }
}
