<?php

namespace ThULB\RecordDriver;

use VuFind\RecordDriver\Summon as OriginalSummon;

/**
 * Overwrites and extends VuFinds standard Summon RecordDriver
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class Summon extends OriginalSummon
{
    public function getURLs()
    {
        $hasNoFulltext = !isset($this->fields['hasFullText']) || !$this->fields['hasFullText'];
        if (isset($this->fields['link']) && $hasNoFulltext) {
            return [
                [
                    'url' => $this->fields['link'],
                    'desc' => $this->translate('get_citation')
                ]
            ];
        } else {
            return parent::getURLs();
        }
    }
}
