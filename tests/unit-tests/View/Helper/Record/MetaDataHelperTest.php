<?php

namespace ThULBTest\View\Helper\Record;

use Box\Spout\Common\Type;
use Box\Spout\Reader\IteratorInterface;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Reader\ReaderInterface;
use Box\Spout\Writer\Common\Sheet;
use ThULB\View\Helper\Record\MetaDataHelper;

/**
 * All unit tests for the MetaDataHelper 
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class MetaDataHelperTest extends \ThULBTest\View\Helper\AbstractViewHelperTest {
   
    public function testSingleRecordQuery()
    {
        $record = $this->getRecordFromFindex('636418014');
        
        if ($record instanceof SolrVZGRecord
            && $record->getMarcRecord() instanceof \File_MARC_Record
            && strpos($record->getTitle(), 'Tractatus')    
        ) {
            return true;
        }
        
        return false;
    }
}
