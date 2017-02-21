<?php

namespace ThULBTest\View\Helper\Root;

use Box\Spout\Common\Type;
use Box\Spout\Reader\IteratorInterface;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Reader\ReaderInterface;
use Box\Spout\Writer\Common\Sheet;
use ThULB\View\Helper\Record\MetaDataHelper;
use ThULB\RecordDriver\SolrVZGRecord;

/**
 * Unit tests for the RecordDataFormatter
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class RecordDataFormatterTest extends \ThULBTest\View\Helper\AbstractViewHelperTest {
   
    public function testSingleRecordQuery()
    {
        $record = $this->getRecordFromFindex('636418014');
        
        $this->assertInstanceOf(SolrVZGRecord::class, $record);
        $this->assertInstanceOf(\File_MARC_Record::class, $record->getMarcRecord());
        $this->assertEquals('Ludwig Wittgenstein: Tractatus logico-philosophicus', $record->getTitle());
    }
}
