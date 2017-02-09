<?php

namespace ThULB\ILS\Driver;
use VuFind\ILS\Driver\PAIA as OriginalPAIA;

/**
 * ThULB extension for the PAIA/DAIA driver
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class PAIA extends OriginalPAIA
{
    const DAIA_DOCUMENT_ID_PREFIX = 'http://uri.gbv.de/document/opac-de-27:';
    
    const DAIA_UNKNOWN_CONTENT_VALUE = 'Unknown';
    
    protected function getStatusString($item)
    {
        $status = 'unknown';
        if (isset($item['available']) && $item['available']) {
            $status = 'available';
        } elseif (isset($item['unavailable']) && $item['unavailable']) {
            $status = 'unavailable';
        }
        
        return $status;
    }
    
    /**
     * Overrides the function in the DAIA driver class, to execute additional
     * steps.
     * 
     * @param string $id           Record Id of the DAIA document in question.
     * @param string $daiaResponse Raw response from DAIA request.
     *
     * @return Array|DOMNode|null   The DAIA document identified by id and
     *                                  type depending on daiaResponseFormat.
     * @throws ILSException
     */
    protected function extractDaiaDoc($id, $daiaResponse)
    {
        return parent::extractDaiaDoc(
                    $id,
                    $this->sanitizeDaiaDocumentIds($daiaResponse)
                );
    }

    /**
     * Returns the value for "callnumber" in VuFind getStatus/getHolding array
     *
     * @param array $item Array with DAIA item data
     *
     * @return string
     */
    protected function getItemCallnumber(&$item)
    {
        $callnumber = isset($item['label']) && !empty($item['label']) ? $item['label'] : self::DAIA_UNKNOWN_CONTENT_VALUE;
        
        if ($this->hasSignatureWithDepartmentId($item)) {
            $callnumber = substr($callnumber, strpos($callnumber, ':') + 1);
        }
        
        return $callnumber;
    }
    
    /**
     * This function ensures, that the condition "DAIA documents should use URIs
     * as value for id" (see DAIA Driver class comment) is fulfilled even for a
     * GBV response.
     * 
     * @see \VuFind\ILS\Driver\DAIA::extractDaiaDoc() for the failing condition
     * @todo relax the condition in vufinds core DAIA driver, because it
     *      contradicts the documentation of DAIA (http://gbv.github.io/daia/daia.html#documents
     *      talks about a "globally unique identifier of the document" and not
     *      about the request identifier in the id field)
     */
    private function sanitizeDaiaDocumentIds($daiaResponse)
    {
        return str_replace(self::DAIA_DOCUMENT_ID_PREFIX, '', $daiaResponse);
    }
    
        /**
     * Parse an array with DAIA status information.
     *
     * @param string $id        Record id for the DAIA array.
     * @param array  $daiaArray Array with raw DAIA status information.
     *
     * @return array            Array with VuFind compatible status information.
     */
    protected function parseDaiaArray($id, $daiaArray)
    {
        $result = [];
        
        $doc_id = null;
        $doc_href = null;
        if (isset($daiaArray['id'])) {
            $doc_id = $daiaArray['id'];
        }
        if (isset($daiaArray['href'])) {
            // url of the document (not needed for VuFind)
            $doc_href = $daiaArray['href'];
        }
        if (isset($daiaArray['message'])) {
            // log messages for debugging
            $this->logMessages($daiaArray['message'], 'document');
        }
        // if one or more items exist, iterate and build result-item
        if (isset($daiaArray['item']) && is_array($daiaArray['item'])) {
            $number = 0;
            foreach ($daiaArray['item'] as $item) {
                $result_item = [];
                $result_item['id'] = $id;
                // custom DAIA field
                $result_item['doc_id'] = $doc_id;
                $result_item['item_id'] = $item['id'];
                // custom DAIA field used in getHoldLink()
                $result_item['ilslink']
                    = (isset($item['href']) ? $item['href'] : $doc_href);
                // about Field
                $result_item['about']
                    = (isset($item['about']) ? $item['about'] : "");
                // count items
                $number++;
                $result_item['number'] = $this->getItemNumber($item, $number);
                // set default value for barcode
                $result_item['barcode'] = $this->getItemBarcode($item);
                // set default value for reserve
                $result_item['reserve'] = $this->getItemReserveStatus($item);
                // get callnumber
                $result_item['callnumber'] = $this->getItemCallnumber($item);
                // get location
                $result_item['location'] = $this->getItemDepartment($item);
                // custom DAIA field
                $result_item['locationid'] = $this->getItemDepartmentId($item);
                // get location link
                $result_item['locationhref'] = $this->getItemDepartmentLink($item);
                // custom DAIA field
                $result_item['storage'] = $this->getItemStorage($item);
                // custom DAIA field
                $result_item['storageid'] = $this->getItemStorageId($item);
                // custom DAIA field
                $result_item['storagehref'] = $this->getItemStorageLink($item);
                // status and availability will be calculated in own function
                $result_item = $this->getItemStatus($item) + $result_item;
                // add result_item to the result array, if at least one relevant
                // information is present
                if ($result_item['callnumber'] !== self::DAIA_UNKNOWN_CONTENT_VALUE
                    || $result_item['location'] !== self::DAIA_UNKNOWN_CONTENT_VALUE
                    || $result_item['about']
                ) {
                    $result[] = $result_item;
                }
            } // end iteration on item
        }

        return $result;
    }
    
    /**
     * Returns the value of item.department.content (e.g. to be used in VuFind
     * getStatus/getHolding array as location)
     * 
     * @param array $item Array with DAIA item data
     * @return string
     */
    protected function getItemDepartment(&$item)
    {
        $itemDepartment = isset($this->config['DepartmentTitles']['default']) ? $this->config['DepartmentTitles']['default'] : parent::getItemDepartment($item);
        
        if ($this->hasSignatureWithDepartmentId($item)) {
            $depID = strstr($item['label'], ':', true);
            if (isset($this->config['DepartmentTitles'][$depID])) {
                $itemDepartment = $this->config['DepartmentTitles'][$depID];
            }
        }
        
        return $itemDepartment;
    }

    /**
     * Returns the value of item.department.id (e.g. to be used in VuFind
     * getStatus/getHolding array as location)
     *
     * @param array $item Array with DAIA item data
     *
     * @return string
     */
    protected function getItemDepartmentId(&$item)
    {
        $itemDepartmentId = parent::getItemDepartmentId($item);
        
        if ($this->hasSignatureWithDepartmentId($item)) {
            $itemDepartmentId = strstr($item['label'], ':', true);
        }
        
        return $itemDepartmentId;
    }

    /**
     * Returns the value of item.department.href (e.g. to be used in VuFind
     * getStatus/getHolding array for linking the location)
     *
     * @param array $item Array with DAIA item data
     *
     * @return string
     */
    protected function getItemDepartmentLink(&$item)
    {
        $itemDepartmentLink = isset($this->config['DepartmentLinks']['default']) ? $this->config['DepartmentLinks']['default'] : parent::getItemDepartmentLink($item);
        
        if ($this->hasSignatureWithDepartmentId($item)) {
            $depID = strstr($item['label'], ':', true);
            if (isset($this->config['DepartmentTitles'][$depID])) {
                $itemDepartmentLink = $this->config['DepartmentLinks'][$depID];
            }
        }
        
        return $itemDepartmentLink;
    }
    
    /**
     * Get if the signature in the label field starts with the department id.
     * 
     * @param array $item Array with DAIA item data
     * @return boolean
     */
    protected function hasSignatureWithDepartmentId(&$item)
    {
        $depPrefix = false;
        
        if (isset($item['label']) && !empty($item['label']) && strpos($item['label'], ':')) {
            $depID = strstr($item['label'], ':', true);
            if (isset($this->config['DepartmentTitles'][$depID])) {
                $depPrefix = true;
            }
        }
        
        return $depPrefix;
    }
}
