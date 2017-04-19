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
    const DAIA_DOCUMENT_ID_PREFIX = 'http://uri.gbv.de/document/opac-de-27:ppn:';
    
    const DAIA_UNKNOWN_CONTENT_VALUE = 'Unknown';

    /**
     * Get Patron Holds
     *
     * This is responsible for retrieving all holds by a specific patron.
     *
     * @param array $patron The patron array from patronLogin
     *
     * @return mixed Array of the patron's holds on success.
     */
    public function getMyHolds($patron)
    {
        // filters for getMyHolds are:
        // status = 1 - reserved (the document is not accessible for the patron yet,
        //              but it will be)
        $filter = ['status' => [1]];
        // get items-docs for given filters
        $items = $this->paiaGetItems($patron, $filter);

        return $this->mapPaiaItems($items, 'myHoldsMapping');
    }
    
    /**
     * Get Patron Loans
     *
     * This is responsible for retrieving all loans (i.e. all checked out items,
     * all storage retrieval requests and all holds with status "provided") by a
     * specific patron.
     *
     * @param array $patron The patron array from patronLogin
     *
     * @return array Array of the patron's transactions on success,
     */
    public function getMyLoans($patron)
    {
        /* 
         * filters for getMyTransactions are:
         * status = 2 - ordered (the document is ordered by the patron)
         *          3 - held (the document is on loan by the patron)
         *          4 - provided (the document is ready to be used by the patron)
         */
        $filter = ['status' => [2, 3, 4]];
        // get items-docs for given filters
        $items = $this->paiaGetItems($patron, $filter);

        return $this->mapPaiaItems($items, 'myLoansMapping');
    }

    /**
     * Get Patron Profile
     *
     * This is responsible for retrieving the profile for a specific patron.
     *
     * @param array $patron The patron array
     *
     * @return array Array of the patron's profile data on success,
     */
    public function getMyProfile($patron)
    {
        $profile = parent::getMyProfile($patron);
        
        if (isset($profile['firstname']) && isset($profile['lastname'])) {
            $profile['name'] = $profile['firstname'] . ' ' . $profile['lastname'];
        }
        
        if (isset($patron['email'])) {
            $profile['email'] = $patron['email'];
        }
        
        if (isset($patron['address'])) {
            $profile['address1'] = $patron['address'];
        }
        
        if (isset($patron['type'])
            && is_array($patron['type'])
            && !empty($patron['type'])
            && preg_match('/de-27:user-type:\d{1}/', $patron['type'][0])
        ) {
            $profile['groupcode'] = preg_replace('/de-27:user-type:(\d{1})/', '$1', $patron['type'][0]);
        }
        
        if (isset($patron['status']) && is_numeric($patron['status'])) {
            $profile['statuscode'] = $patron['status'];
        }
        
        return $profile;
    }
    
    protected function myLoansMapping($items)
    {
        $results = [];

        foreach ($items as $doc) {
            $result = [];
            // canrenew (0..1) whether a document can be renewed (bool)
            $result['renewable'] = (isset($doc['canrenew']))
                ? $doc['canrenew'] : false;

            // item (0..1) URI of a particular copy
            $result['item_id'] = (isset($doc['item']) ? $doc['item'] : '');

            $result['renew_details']
                = ($result['renewable']) ? $result['item_id'] : '';

            // edition (0..1)  URI of a the document (no particular copy)
            // hook for retrieving alternative ItemId in case PAIA does not
            // the needed id
            $result['id'] = (isset($doc['edition'])
                ? $this->getAlternativeItemId($doc['edition']) : '');

            // requested (0..1) URI that was originally requested

            // about (0..1) textual description of the document
            $result['title'] = (isset($doc['about']) ? $doc['about'] : null);

            // queue (0..1) number of waiting requests for the document or item
            $result['request'] = (isset($doc['queue']) ? $doc['queue'] : null);

            // renewals (0..1) number of times the document has been renewed
            $result['renew'] = (isset($doc['renewals']) ? $doc['renewals'] : null);

            // reminder (0..1) number of times the patron has been reminded
            $result['reminder'] = (
                isset($doc['reminder']) ? $doc['reminder'] : null
            );

            // custom PAIA field
            // starttime (0..1) date and time when the status began
            $result['startTime'] = (isset($doc['starttime'])
                ? $this->convertDatetime($doc['starttime']) : '');

            // endtime (0..1) date and time when the status will expire
            $result['dueTime'] = (isset($doc['endtime'])
                ? $this->convertDatetime($doc['endtime']) : '');

            if ($doc['status'] == '4') {
                $result['expire'] = (isset($doc['endtime'])
                    ? $this->convertDatetime($doc['endtime']) : '');
            } elseif ($doc['status'] == '3') {
                // duedate (0..1) date when the current status will expire (deprecated)
                $result['duedate'] = (isset($doc['duedate'])
                    ? $this->convertDate($doc['duedate']) : '');
            }
            
            // storage (0..1) textual description of location of the document
            $result['location'] = (isset($doc['storage']) && $doc['status'] != 3) ? $doc['storage'] : null;

            // cancancel (0..1) whether an ordered or provided document can be
            // canceled

            // error (0..1) error message, for instance if a request was rejected
            $result['message'] = (isset($doc['error']) ? $doc['error'] : '');

            // storageid (0..1) location URI

            // PAIA custom field
            // label (0..1) call number, shelf mark or similar item label
            $result['callnumber'] = $this->getCallNumber($doc);
            
            // status: provided (the document is ready to be used by the patron)
            $result['available'] = $doc['status'] == 4 ? true : false;
            
            $result['queue'] = isset($doc['queue']) ? $doc['queue'] : 0;

            // Optional VuFind fields
            /*
            $result['barcode'] = null;
            $result['dueStatus'] = null;
            $result['renewLimit'] = "1";
            $result['volume'] = null;
            $result['publication_year'] = null;
            $result['isbn'] = null;
            $result['issn'] = null;
            $result['oclc'] = null;
            $result['upc'] = null;
            $result['institution_name'] = null;
            */

            $results[] = $result;
        }

        return $results;
    }

    /**
     * PAIA support method to retrieve needed ItemId in case PAIA-response does not
     * contain it
     *
     * @param string $id itemId
     *
     * @return string $id
     */
    protected function getAlternativeItemId($id)
    {
        return str_replace(self::DAIA_DOCUMENT_ID_PREFIX, '', $id);
    }
    
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
     * Returns the value for "callnumber" in VuFind getStatus/getHolding array
     *
     * @param array $item Array with DAIA item data
     *
     * @return string
     */
    protected function getItemCallnumber($item)
    {
        $callnumber = isset($item['label']) && !empty($item['label']) ? $item['label'] : self::DAIA_UNKNOWN_CONTENT_VALUE;
        
        if ($this->hasSignatureWithDepartmentId($item)) {
            $callnumber = substr($callnumber, strpos($callnumber, ':') + 1);
        }
        
        return $callnumber;
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
                    || $result_item['about']
                ) {
                    $result[] = $result_item;
                }
            } // end iteration on item
        }

        return $result;
    }

    /**
     * Returns the value of item.storage.content instead of 
     * item.department.content (e.g. to be used in VuFind getStatus/getHolding
     * array as location)
     *
     * @param array $item Array with DAIA item data
     *
     * @return string
     */
    protected function getItemDepartment($item)
    {
        return isset($item['storage']) && isset($item['storage']['content'])
        && !empty($item['storage']['content'])
            ? $item['storage']['content']
            : parent::getItemDepartment($item);
    }

    /**
     * Returns the value of item.storage.id instead of item.department.id (e.g.
     * to be used in VuFind getStatus/getHolding array as location)
     *
     * @param array $item Array with DAIA item data
     *
     * @return string
     */
    protected function getItemDepartmentId($item)
    {
        return isset($item['storage']) && isset($item['storage']['id'])
            ? $item['storage']['id'] : parent::getItemDepartmentId($item);
    }

    /**
     * Returns the value of item.storage.href instead of item.department.href
     * (e.g. to be used in VuFind getStatus/getHolding array for linking the
     * location)
     *
     * @param array $item Array with DAIA item data
     *
     * @return string
     */
    protected function getItemDepartmentLink($item)
    {
        return isset($item['storage']['href'])
            ? $item['storage']['href'] : parent::getItemDepartmentLink($item);
    }
    
    /**
     * Get if the signature in the label field starts with the department id.
     * 
     * @param array $item Array with DAIA item data
     * @return boolean
     */
    protected function hasSignatureWithDepartmentId(&$item)
    {
        $hasDepPrefix = false;
        
        if (isset($item['label'])
            && !empty($item['label'])
            && strpos($item['label'], ':')
        ) {
            $depID = strstr($item['label'], ':', true);
            if (isset($this->config['DepartmentTitles'][$depID])) {
                $hasDepPrefix = true;
            }
        }
        
        return $hasDepPrefix;
    }
}
