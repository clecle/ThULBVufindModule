<?php
namespace ThULB\View\Helper\Record;

use File_MARC_Record;
use ThULB\RecordDriver\SolrVZGRecord;
use Zend\View\Helper\AbstractHelper;

class MetaDataHelper extends AbstractHelper
{
    /**
     * Determines the title of a single MARC record
     * 
     * @param File_MARC_Record|null $record
     * @return String
     */
    public function title($record = null)
    {   
        if (is_null($record)) {
            $record = $this->view->driver;
        }
        
        if ($record instanceof SolrVZGRecord) {
// @todo: add custom title logic based on the marc record
//            /** @var File_MARC_Record $marcRecord */
//            $marcRecord = $record->getMarcRecord();
            
            return $record->getTitle();
        }
        
        return $this->view->transEsc('record_title_unknown');
    }
    
}