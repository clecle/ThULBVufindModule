<?php

namespace ThULB\RecordDriver;

class SolrVZGRecord extends \VuFind\RecordDriver\SolrMarc
{
    const PPN_LINK_ID_PREFIX = 'DE-601';
    const ZDB_LINK_ID_PREFIX = 'DE-600';
    const DNB_LINK_ID_PREFIX = 'DE-101';
    
    /**
     * Short title of the record.
     *
     * @var string 
     */
    protected $shortTitle;
    
    /**
     * Title of the record.
     *
     * @var string
     */
    protected $title;


    /**
     * Get the short (pre-subtitle) title of the record.
     *
     * @return string
     */
    public function getShortTitle()
    {
        if (is_null($this->shortTitle)) {
            $shortTitle = $this->getFormattedMarcData(
                    '245a : 245b',
                    [' = ',' =', '= ', ' : ', ' :', ': ']
                ) ?: $this->getFormattedMarcData('490v: 490a');

            if ($shortTitle === '')
            {
                $shortTitle = isset($this->fields['title_short']) ?
                    is_array($this->fields['title_short']) ?
                    $this->fields['title_short'][0] : $this->fields['title_short'] : '';
            }

            $this->shortTitle = $shortTitle;
        }
        
        return $this->shortTitle;
    }

    public function getTitle()
    {
        if (is_null($this->title)) {
            $title = $this->getFormattedMarcData('245n: (245p. (245a : 245b))') ?: $this->getFormattedMarcData('490v: 490a');

            if ($title === '') {
                isset($this->fields['title']) ?
                            is_array($this->fields['title']) ?
                            $this->fields['title'][0] : $this->fields['title'] : '';
            }

            $this->title = $title;
        }
        
        return $this->title;
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
     * Generates a single line with basic publication information including the
     * first location of the publication, the publisher, the year and the 
     * edition.
     * 
     * @return String
     */
    public function getReducedPublicationInfo()
    {
        return $this->getFormattedMarcData('(((264a : 264b), 264c). 250a)');
    }
    
    public function getPartInfo()
    {
        $nSubfields = $this->getFieldArray('245', ['n'], false);
        $pSubfields = $this->getFieldArray('245', ['p'], false);
        
        $numOfEntries = max([count($nSubfields), count($pSubfields)]);
        
        $partInfo = '';
        for ($i = 0; $i < $numOfEntries; $i++) {
            $n = (isset($nSubfields[$i]) && $nSubfields[$i] !== '[...]') ? $nSubfields[$i] : '';
            $p = (isset($pSubfields[$i]) && $pSubfields[$i] !== '[...]') ? $pSubfields[$i] : '';
            $separator = ($n && $p) ? ': ' : '';
            $partInfo .= (($i > 0 && ($n || $p)) ? ' ; ' : '') . 
                             $n . $separator . $p;
        }
        
        return $partInfo;
    }
    
    /**
     * Get a formatted string from different MARC fields 
     * 
     * @param string $format    Describes the desired formatted output; MARC 
     *                          fields and their subfields are coded with a 3
     *                          digit number that is immediately followed by the
     *                          character of the subfield (e.g. "260a");
     *                          to make hints for the separator priority in case
     *                          of missing MARC fields, simple parentheses are
     *                          used; examples:
     *                          - "264a : 264b, 264c. 250a": no information for
     *                            separator priority - they are all treated as
     *                            postfix; if e.g 264b is missing, the output is 
     *                            "264a : 264c. 250a"
     *                          - "((264a : 264b), 264c). 250a": the evaluation 
     *                            order is provided; if e.g. 264b is missing,
     *                            the output is "264a, 264c. 250a"
     * @param array $predefSeparators An array of strings, that might occur in
     *                                the MARC field entries and make separators
     *                                in the format string obsolete
     */
    protected function getFormattedMarcData($format, $predefSeparators = [])
    {   
        // get all MARC data that is required (only first field values)
        $marcData = [];
        $marcFieldStrings = [];
        preg_match_all('/[\d]{3}[a-z]{1}/', $format, $marcFieldStrings, PREG_OFFSET_CAPTURE);
        foreach ($marcFieldStrings[0] as $i => $marcFieldInfo) {
            $fieldNumber = substr($marcFieldInfo[0], 0, 3);
            $subfieldChar = substr($marcFieldInfo[0], 3);
            $value = $this->getFirstFieldValue($fieldNumber, [$subfieldChar]);
            if (!is_null($value)) {
                $marcData[$fieldNumber . $subfieldChar] = $value;
                $replacement = 'T';
                // check for separators in the marc field and marc the separator
                // in the format string as removable
                foreach ($predefSeparators as $separator) {
                    if (substr($value, 0, strlen($separator)) === $separator) {
                        $replacement = 'ST';
                    } else if ((substr($value, -strlen($separator)) === $separator)) {
                        $replacement = 'TS';
                    }
                }
                $format = str_replace($fieldNumber . $subfieldChar, $replacement, $format);
            } else {
                $format = str_replace($fieldNumber . $subfieldChar, 'F', $format);
            }
        }
        
        // Eliminate all missing fields and surrounding content inside the
        // parantheses:
        $format = preg_replace('/[^T\(\)]*F[^T\(\)]*/', '', $format);
        // Remove all content in parantheses, that doesn't represent existing
        // Marc fields together with surrounding content
        $format = preg_replace('/[^T\(\)]*\([^T]*\)[^T\(\)]*/', '', $format);
        // Remove separators for fields, where they are given with the field
        // content
        $format = preg_replace('/([^T\(\)]+S)|(S[^T\(\)]+)/', ' ', $format);
        // Transform to a valid formatter string
        $format = str_replace('T', '%s', str_replace('(', '', str_replace(')', '', $format)));
        
        return vsprintf($format, $marcData);
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
            '810' => ['a', 'p'],
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
     * Support method for getSeries() -- given a field specification, look for
     * series information in the MARC record.
     *
     * @param array $fieldInfo Associative array of field => subfield information
     * (used to find series name)
     *
     * @return array
     */
    protected function getSeriesFromMARC($fieldInfo)
    {
        $matches = [];

        // Loop through the field specification....
        foreach ($fieldInfo as $field => $subfields) {
            // Did we find any matching fields?
            $series = $this->getMarcRecord()->getFields($field);
            if (is_array($series)) {
                foreach ($series as $currentField) {
                    // Can we find a name using the specified subfield list?
                    $name = $this->getSubfieldArray($currentField, $subfields);
                    if (!isset($name[0])) {
                        $volume = $this->getSubfieldArray($currentField, ['v']);
                        $name = $this->getConditionalFieldArray('490', ['a'], true, ' ', ['v' => $volume[0]]);
                    }
                    
                    if (isset($name[0])) {
                        $currentArray = ['name' => $name[0]];

                        // Can we find a number in subfield v?  (Note that number is
                        // always in subfield v regardless of whether we are dealing
                        // with 440, 490, 800 or 830 -- hence the hard-coded array
                        // rather than another parameter in $fieldInfo).
                        $number
                            = $this->getSubfieldArray($currentField, ['v']);
                        if (isset($number[0])) {
                            $currentArray['number'] = $number[0];
                        }
                        
                        $id = $this->getSubfieldArray($currentField, ['w'], false);
                        foreach ($id as $rawId) {
                            if (strpos($rawId, '(DE-601)') === 0) {
                                $currentArray['id'] = substr($rawId, 8);
                                break;
                            }
                        }

                        // Save the current match:
                        $matches[] = $currentArray;
                    }
                }
            }
        }

        return $matches;
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
    
    /**
     * Criteria:
     *    Leader 07 = s
     *  OR
     *    Leader 19 = a
     *  OR
     *    Leader 007 00 = c
     *    AND
     *    Leader 007 01 = r
     * @return boolean
     * 
     * @deprecated
     * 
     */
    public function isOnlineOnlyRecord()
    {
      $leader = $this->getMarcRecord()->getLeader();
      if ( strtoupper($leader[7] ) == "S" ) {
        return true;
      }
      if ( strtoupper($leader[19]) == "A" ) {
        return true;
      }
      $val = $this->getMarcRecord()->getFields('007');
      if ( !empty($val) ) {
        $val2 = $val[0]->getData();
        if ( strtoupper(substr($val2, 0, 2)) == "CR" ) {
          return true;
        }
      }

      return false;
    }
    
    public function isNewsPaper()
    {
      $leader = $this->getMarcRecord()->getLeader();
      if ( strtoupper($leader[7] ) == "S" ) {
        return true;
      }

      return false;
    }

     /**
      * Return an array of all OnlineHoldings from MARCRecord
      * Field 981: for Links
      * Field 980: for description
      * Field 982: 
      * 
      * $txt = Text for displaying the link
      * $url = url to OnlineContent
      * $more = further description (PICA 4801)
      * $tmp = ELS-gif for Higliting ELS Links
      * 
      * @return array
      */
    public function getOnlineHoldings()
    {
      $retVal = [];
      
      /* extract all LINKS form MARC 981 */
      $links = $this->getConditionalFieldArray('981', ['1', 'y', 'r', 'w'], true, '|', ['2' => '31']);

      if ( !empty($links) ){
        /* what kind of LINKS do we have?
         * is there more Information in MARC 980 / 982?
         */
        foreach ( $links as $link ) {
          $more = "";
          list($id, $txt, $url) = explode("|", $link);
       
          /* do we have a picture? f.e. ELS-gif */
          if ( substr($txt, -3) == "gif" ) {
            $retVal[$id] = $txt;
            continue;
          }

          /* seems that the real LINK is in 981y if 981r or w is empty... */
          if ( empty($txt) ) {
            $txt = $url;
          }
          /* ... and vice versa */
          if ( empty($url) ) {
            $url = $txt;
            $txt = "fulltext";
          }

          /* Now, we are ready to extract extra-information */
          $details = $this->getConditionalFieldArray('980', ['g', 'k'], false, '', ['2' => '31', '1' => $id]);
          $corporates = $this->getConditionalFieldArray('982', ['a'], false, '', ['2' => '31', '1' => $id]);
          
          if ( !empty($details) ) {
            foreach ($details as $detail) {
               $more .= $detail."<br>";
            }
          }
          if ( !empty($corporates) ) {
            foreach ($corporates as $corporate) {
              $more .= $corporate."<br>";
            }
          }
          
          /* extract Info/Links with same ID
           * thats the case, if we have an ELS-gif,
           * so we assume, that the gif is set-up before.
           * f.e.
           * 981 |2 31  |1 00  |w http://kataloge.thulb.uni-jena.de/img_psi/2.0/logos/eLS.gif 
           * 981 |2 31  |1 00  |y Volltext  |w http://mybib.thulb.uni-jena.de/els/browser/open/557127483  
           */
          $tmp = $retVal[$id];
          $retVal[$id] = $txt . "|" . $url . "|" . $more . "|" . $tmp;
        }
      } else {
        $retVal = "";
      }
      return $retVal;
    }
    
    /**
     * Get the Hierarchy Type (false if none)
     *
     * @return string|bool
     */
    public function getHierarchyType()
    {
        $hierarchyType = isset($this->fields['hierarchytype'])
            ? $this->fields['hierarchytype'] : false;
        if (!$hierarchyType) {
            $hierarchyType = isset($this->mainConfig->Hierarchy->driver)
                ? $this->mainConfig->Hierarchy->driver : false;
        }
        return $hierarchyType;
        
        return false;
    }
}
