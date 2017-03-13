<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ThULBTest\View\Helper\Root;

/**
 * Description of RelatedItemsMetadataTest
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class RelatedItemsMetadataTest extends AbstractRecordDataFormatterTest
{
    protected $sheetName = 'Ähnliche Datensätze';
    protected $metadataKey = 'Related Items';
    protected $recordDriverFunction = 'getAllRecordLinks';
    protected $template = 'data-allRecordLinks.phtml';
}
