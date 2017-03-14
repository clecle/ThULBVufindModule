<?php

namespace ThULB\RecordDriver;

class SolrVZGRecord extends \VuFind\RecordDriver\SolrMarc
{
    const PPN_LINK_ID_PREFIX = 'DE-601';
    const ZDB_LINK_ID_PREFIX = 'DE-600';
    const DNB_LINK_ID_PREFIX = 'DE-101';
    
    /**
     * Get the short (pre-subtitle) title of the record.
     *
     * @return string
     */
    public function getShortTitle()
    {
        return isset($this->fields['title_short']) ?
            is_array($this->fields['title_short']) ?
            $this->fields['title_short'][0] : $this->fields['title_short'] : '';
    }

    /**
     * Get the full title of the record.
     *
     * @return string
     */
    public function getTitle()
    {
        return isset($this->fields['title']) ?
            is_array($this->fields['title']) ?
            $this->fields['title'][0] : $this->fields['title'] : '';
    }

    /**
     * Get the subtitle of the record.
     *
     * @return string
     */
    public function getSubtitle()
    {
        return isset($this->fields['title_sub']) ?
            is_array($this->fields['title_sub']) ?
            $this->fields['title_sub'][0] : $this->fields['title_sub'] : '';
    }

    /**
     * Returns one of three things: a full URL to a thumbnail preview of the record
     * if an image is available in an external system; an array of parameters to
     * send to VuFind's internal cover generator if no fixed URL exists; or false
     * if no thumbnail can be generated.
     *
     * @param string $size Size of thumbnail (small, medium or large -- small is
     * default).
     *
     * @return string|array|bool
     */
    public function getThumbnail($size = 'small')
    {
        $params = parent::getThumbnail($size);
        
        $params['contenttype'] = $this->fields['format'] ? $this->fields['format'][0] : '';
        
        return $params;
    }

    /**
     * Get basic classification numbers of the record.
     *
     * @return array
     */
    public function getBasicClassification()
    {
        $fields = $this->getConditionalFieldArray('084', ['a'], true, ' ', ['2' => 'bcl']);
        
        return $fields;
    }

    /**
     * Get classification numbers of the record in the "Thüringen-Bibliographie".
     *
     * @return array
     */
    public function getThuBiblioClassification()
    {
        $fields = $this->getConditionalFieldArray('983', ['a'], true, ' ', ['2' => '31']);
        
        return $fields;
    }
    
    /**
     * extract ZDB Number from 035 $a
     * 
     * searches for a string like "(DE-599)ZDBNNNNNN"
     * where DE-599 stands for ISIL - Staatsbibliothek Berlin
     * followed by ZDB Number
     * 
     * @return array
     */
    public function getZDBID() {
        $id_nums = $this->getFieldArray('035', ['a']);
        $zdb_nums[] = "";

        foreach ($id_nums as $id_num) {
            if (strpos($id_num, '(DE-599)ZDB') !== false) {
                array_push($zdb_nums, substr($id_num, 11));
            }
        }
        
        return $zdb_nums;
    }
    
    /**
     *  Erscheinungsverlauf from 362 $a
     * 
     * @TODO repeatable?
     * 
     * @return string
     */
    public function getNumbering() {
        return $this->getFirstFieldValue('362', ['a']);
    }
    
    /**
     * Erscheinungsverlauf from 515 $a
     * 
     * not repeatable
     * 
     * @return string
     */
    public function getNumberingPeculiarities() {
        return $this->getFirstFieldValue('515', ['a']);
    }
    
    /**
     * Anmerkungen from 546 $a
     * 
     * not repeatable
     * 
     * @return string
     */
    public function getLanguageNotes() {
        return $this->getFirstFieldValue('546', ['a']);
    }

    /**
     * Fingerprint information from Marc Field 026
     * 
     * not repeatable
     * 
     * @return string
     */
    public function getFingerprint()
    {
        return $this->getFieldArray('026', ['e', '5'], false);
    }
    
    // Bibliographic citation from Marc field 510
    public function getBibliographicCitation()
    {
        return $this->getFirstFieldValue('510', ['a']);
    }
    
    /**
     * Get an array of physical descriptions of the item.
     *
     * @return array
     */
    public function getPhysicalDescriptions()
    {
        $fields = $this->getMarcRecord()->getFields('300');
        
        $physicalDescriptions = [];
        foreach ($fields as $singleField) {
            $pdPt1 = $this->getSubfieldArray($singleField, ['a', 'b'], true, ' : ');
            $pdPt2 = $this->getSubfieldArray($singleField, ['c', 'd', 'e'], true, ' ; ');
            
            if (!empty($pdPt1) && !empty($pdPt2)) {
                $physicalDescriptions[] = $pdPt1[0] . ' ; ' . $pdPt2[0];
            } else if (!empty($pdPt1)) {
                $physicalDescriptions[] = $pdPt1[0];
            } else if (!empty($pdPt2)) {
                $physicalDescriptions[] = $pdPt2[0];
            }
        }
        
        return $physicalDescriptions;
    }
    
    /**
     * Get the scale of a map.
     * 
     * @return string
     */
    public function getCartographicScale()
    {
        return $this->getFieldArray('255', ['a'], true, ' ; ');
    }
    
    /**
     * Get the projection of a map.
     * 
     * @return string
     */
    public function getCartographicProjection()
    {
        return $this->getFirstFieldValue('255', ['b']);
    }
    
    /**
     * Get the coordinates of a map.
     * 
     * @return string
     */
    public function getCartographicCoordinates()
    {
        return $this->getFirstFieldValue('255', ['c']);
    }
    
    /**
     * Get the equinox of a map.
     * 
     * @return string
     */
    public function getCartographicEquinox()
    {
        return $this->getFirstFieldValue('255', ['e']);
    }
    
    /**
     * Returns the array element for the 'getAllRecordLinks' method
     *
     * @param File_MARC_Data_Field $field Field to examine
     *
     * @return array|bool                 Array on success, boolean false if no
     * valid link could be found in the data.
     */
    protected function getFieldData($field)
    {
        $leader = $this->getMarcRecord()->getLeader();
        // Make sure that there is a t field to be displayed:
        if ($title = $field->getSubfield('t')) {
            $title = $title->getData();
        } else if (strtolower($leader[7]) === 'm'
            && strtolower($leader[19]) === 'c'
        ) {
            $title = $this->getFirstFieldValue('245', ['a']);
        } else {
            $title = false;
        }

        $linkTypeSetting = isset($this->mainConfig->Record->marc_links_link_types)
            ? $this->mainConfig->Record->marc_links_link_types
            : 'id,isbn,issn,zdb,dnb,title';
        $linkTypes = explode(',', $linkTypeSetting);
        $linkFields = $field->getSubfields('w');

        // Run through the link types specified in the config.
        // For each type, check field for reference
        // If reference found, exit loop and go straight to end
        // If no reference found, check the next link type instead
        foreach ($linkTypes as $linkType) {
            switch (trim($linkType)){
            case 'id':
                foreach ($linkFields as $current) {
                    $bibLink = $this->getIdFromLinkingField($current, self::PPN_LINK_ID_PREFIX);
                    if ($bibLink) {
                        $link = ['type' => 'bib', 'value' => $bibLink];
                    }
                }
                break;
            case 'isbn':
                if ($isbn = $field->getSubfield('z')) {
                    $link = [
                        'type' => 'isbn', 'value' => trim($isbn->getData()),
                        'exclude' => $this->getUniqueId()
                    ];
                }
                break;
            case 'issn':
                if ($issn = $field->getSubfield('x')) {
                    $link = [
                        'type' => 'issn', 'value' => trim($issn->getData()),
                        'exclude' => $this->getUniqueId()
                    ];
                }
                break;
            case 'zdb':
                foreach ($linkFields as $current) {
                    $bibLink = $this->getIdFromLinkingField($current, self::ZDB_LINK_ID_PREFIX);
                    if ($bibLink) {
                        $link = ['type' => 'zdb', 'value' => $bibLink];
                    }
                }
                break;
            case 'dnb':
                foreach ($linkFields as $current) {
                    $bibLink = $this->getIdFromLinkingField($current, self::DNB_LINK_ID_PREFIX);
                    if ($bibLink) {
                        $link = ['type' => 'dnb', 'value' => $bibLink];
                    }
                }
                break;
            case 'title':
                if ($title) {
                    $link = ['type' => 'title', 'value' => $title];
                }
                break;
            }
            // Exit loop if we have a link
            if (isset($link)) {
                break;
            }
        }
        
        $pages = $field->getSubfield('g');
        // Make sure we have something to display:
        return !isset($link) ? false : [
            'title' => $this->getRecordLinkNote($field),
            'value' => $title ? $title : 'Link',
            'link'  => $link,
            'pages' => $pages ? $pages->getData() : ''
        ];
    }

    /**
     * Get an array of all series names containing the record.  Array entries may
     * be either the name string, or an associative array with 'name' and 'number'
     * keys.
     *
     * @return array
     */
    public function getSeries()
    {
        $matches = [];

        // First check the 440, 800 and 830 fields for series information:
        $primaryFields = [
            '440' => ['a', 'p'],
            '800' => ['a', 'b', 'c', 'd', 'f', 'p', 'q', 't'],
            '830' => ['a', 'p']];
        $matches = $this->getSeriesFromMARC($primaryFields);
        if (!empty($matches)) {
            return $matches;
        }

        // Now check 490 and display it only if 440/800/830 were empty:
        $secondaryFields = ['490' => ['a']];
        $matches = $this->getSeriesFromMARC($secondaryFields);
        if (!empty($matches)) {
            return $matches;
        }

        // Still no results found?  Resort to the Solr-based method just in case!
        return parent::getSeries();
    }
    
    /**
     * Return an array of all values extracted from the specified field/subfield
     * combination.  If multiple subfields are specified and $concat is true, they
     * will be concatenated together in the order listed -- each entry in the array
     * will correspond with a single MARC field.  If $concat is false, the return
     * array will contain separate entries for separate subfields. If an conditions
     * array is provided with subfield-value pairs, only those entries are selected,
     * that have a subfiled with that value.
     *
     * @param string $field      The MARC field number to read
     * @param array  $subfields  The MARC subfield codes to read
     * @param bool   $concat     Should we concatenate subfields?
     * @param string $separator  Separator string (used only when $concat === true)
     * @param array  $conditions contains key value pairs with a subfield as key
     *                           and the expected subfield content as value
     * 
     * @see VuFind\RecordDriver\SolrMarc::getFieldArray() for the original function
     *
     * @return array
     */
    protected function getConditionalFieldArray($field, $subfields = null, $concat = true,
        $separator = ' ', $conditions = []
    ) { 
        // Default to subfield a if nothing is specified.
        if (!is_array($subfields)) {
            $subfields = ['a'];
        }

        // Initialize return array
        $matches = [];

        // Try to look up the specified field, return empty array if it doesn't
        // exist.
        $fields = $this->getMarcRecord()->getFields($field);
        if (!is_array($fields)) {
            return $matches;
        }

        // Extract all the requested subfields, if applicable.
        foreach ($fields as $currentField) {
            foreach ($conditions as $conditionSubfield => $conditionValue) {
                $check = $this->getSubfieldArray($currentField, [$conditionSubfield]);
                if (!in_array($conditionValue, $check)) {
                    continue 2;
                }
            }
            $next = $next = $this
                ->getSubfieldArray($currentField, $subfields, $concat, $separator);
            $matches = array_merge($matches, $next);
        }

        return $matches;
    }
}
