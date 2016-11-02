<?php

namespace ThULB\View\Helper\Record;

use Zend\View\Helper\AbstractHelper;

class HoldingHelper extends AbstractHelper
{
    
    public function getAvailability(&$itemRow)
    {
        // AJAX Check record?
        $check = isset($itemRow['check']) && $itemRow['check'];
        $checkStorageRetrievalRequest = isset($itemRow['checkStorageRetrievalRequest']) && $itemRow['checkStorageRetrievalRequest'];
        $checkILLRequest = isset($itemRow['checkILLRequest']) && $itemRow['checkILLRequest'];
        // AJAX block record?
        $block = !$check && isset($itemRow['addLink']) && $itemRow['addLink'] === 'block';
        $blockStorageRetrievalRequest = !$checkStorageRetrievalRequest && isset($itemRow['addStorageRetrievalRequestLink']) && $itemRow['addStorageRetrievalRequestLink'] === 'block';
        $blockILLRequest = !$checkILLRequest && isset($itemRow['addILLRequestLink']) && $itemRow['addILLRequestLink'] === 'block';
        
        $availabilityString = '';
        
        if (isset($itemRow['barcode']) && $itemRow['barcode'] != "") {
            if ($itemRow['reserve'] == "Y") {
                $availabilityString .= '<link property=\"availability" href="http://schema.org/InStoreOnly" />';
                $availabilityString .= $this->view->transEsc("On Reserve - Ask at Circulation Desk") . '<br />';
            }
            if (isset($itemRow['use_unknown_message']) && $itemRow['use_unknown_message']) {
                $availabilityString .= '<span class="text-muted">' . $this->view->transEsc("status_unknown_message") . '</span>';
            } else {
                if ($itemRow['availability']) {
                    /* Begin Available Items (Holds) */
                    $availabilityString .= '<span class="text-success">' . $this->view->transEsc("Available") . '<link property="availability" href="http://schema.org/InStock" /></span>';
                    if (!$block && isset($itemRow['link']) && $itemRow['link']) {
                        $availabilityString .= '<a class="' . ($check ? 'checkRequest ' : '') . 'placehold" data-lightbox href="' . $this->view->recordLink()->getRequestUrl($itemRow['link']) . '"><i class="fa fa-flag" aria-hidden="true"></i>&nbsp;' . $this->view->transEsc($check ? "Check Hold" : "Place a Hold") . '</a>';
                    }
                    if (!$blockStorageRetrievalRequest && isset($itemRow['storageRetrievalRequestLink']) && $itemRow['storageRetrievalRequestLink']) {
                        $availabilityString .= '<a class="' . ($checkStorageRetrievalRequest ? 'checkStorageRetrievalRequest ' : '') . 'placeStorageRetrievalRequest" data-lightbox href="' . $this->view->recordLink()->getRequestUrl($itemRow['storageRetrievalRequestLink']) . '"><i class="fa fa-flag" aria-hidden="true"></i>&nbsp;' . $this->view->transEsc($checkStorageRetrievalRequest ? "storage_retrieval_request_check_text" : "storage_retrieval_request_place_text") . '</a>';
                    }
                    if (!$blockILLRequest && isset($itemRow['ILLRequestLink']) && $itemRow['ILLRequestLink']) {
                        $availabilityString .= '<a class="' . ($checkILLRequest ? 'checkILLRequest ' : '') . 'placeILLRequest" data-lightbox href="' . $this->view->recordLink()->getRequestUrl($itemRow['ILLRequestLink']) . '"><i class="fa fa-flag" aria-hidden="true"></i>&nbsp;' . $this->view->transEsc($checkILLRequest ? "ill_request_check_text" : "ill_request_place_text") . '</a>';
                    }
                } else {
                    /* Begin Unavailable Items (Recalls) */
                    $availabilityString .= '<span class="text-danger">' . $this->view->transEsc('ils_hold_item_' . $itemRow['status']) . '<link property="availability" href="http://schema.org/OutOfStock" /></span>';
                    if (isset($itemRow['returnDate']) && $itemRow['returnDate']) {
                        $availabilityString .= '&ndash; <span class="small">' . $this->view->escapeHtml($itemRow['returnDate']) . '</span>';
                    }
                    if (isset($itemRow['duedate']) && $itemRow['duedate']) {
                        $availabilityString .= '&ndash; <span class="small">' . $this->view->transEsc("Due") . ': ' . $this->view->escapeHtml($itemRow['duedate']) . '</span>';
                    }
                    if (isset($itemRow['requests_placed']) && $itemRow['requests_placed'] > 0) {
                        $availabilityString .= '<span>' . $this->view->transEsc("Requests") . ': ' . $this->view->escapeHtml($itemRow['requests_placed']) . '</span>';
                    }
                    if (!$block && isset($itemRow['link']) && $itemRow['link']) {
                        $availabilityString .= '<a class="' . ($check ? 'checkRequest' : '') . 'placehold" data-lightbox href="' . $this->view->recordLink()->getRequestUrl($itemRow['link']) . '"><i class="fa fa-flag" aria-hidden="true"></i>&nbsp;' . $this->view->transEsc($check ? "Check Recall" : "Recall This") . '</a>';
                    }
                }
            }
            /* Embed item structured data: library, barcode, call number */
            if ($itemRow['location']) {
                $availabilityString .= '<meta property="seller" content="' . $this->view->escapeHtmlAttr($itemRow['location']) . '" />';
            }
            if ($itemRow['barcode']) {
                $availabilityString .= '<meta property="serialNumber" content="' . $this->view->escapeHtmlAttr($itemRow['barcode']) . '" />';
            }
            if ($itemRow['callnumber']) {
                $availabilityString .= '<meta property="sku" content="' . $this->view->escapeHtmlAttr($itemRow['callnumber']) . '" />';
            }
            /* Declare that the item is to be borrowed, not for sale */
            $availabilityString .= '<link property="businessFunction" href="http://purl.org/goodrelations/v1#LeaseOut" />';
            $availabilityString .= '<link property="itemOffered" href="#record" />';
        }
        
        return $availabilityString;
    }
    
    public function getLocation(&$item)
    {
        return 'ITEM_LOCATION_PLACEHOLDER';
    }
    
    public function getCallNumber($item)
    {
        return 'ITEM_CALLNUMBER_PLACEHOLDER';
    }
            
    public function blabberblubb($text)
    {
      return 'holdings: ' . $text;
    }

}