<?php

namespace ThULB\RecordDriver;

class SolrVZGRecord extends \VuFind\RecordDriver\SolrMarc
{
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
     * Get credits of people involved in production of the item.
     *
     * @return array
     */
    public function getBasicClassification()
    {
        $fields = $this->getConditionalFieldArray('084', ['a'], true, ' ', ['2' => 'bcl']);
        
        return $fields;
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
