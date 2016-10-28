<?php

namespace ThULB\View\Helper\Record;

use File_MARC_Record;
use Zend\View\Helper\AbstractHelper;

class MetaDataHelper extends AbstractHelper
{

    public function Title(/*SolrMarc $record*/String $txt)
    {
      /** @var File_MARC_Record $marcRecord */
/*      $marcRecord = $record->getMarcRecord();
      $field = $marcRecord->getField('245');
*/
      $ret = "meatadate: ".$txt;
      return $ret;
    }
   
}