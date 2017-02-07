<?php

namespace ThULB\View\Helper\Record;

use Zend\View\Helper\AbstractHelper;

class HoldingHelper extends AbstractHelper
{
    public function getStatus(&$item)
    {
        $loan_availability = false;
        $presentation_availability = false;
        $interloan_availability = false;
        foreach ($item[services] as $i) {
            switch($i) {
                case "loan":
                $loan_availability = true;
            case "presentation":
                $presentation_availability = true;
            /*case "interloan"*/
            }
        }

        $availabilityString = "";
    
        if (isset($item['message'])) {
            $this->view->transEsc($item['message']);
        } else {
            if ($item['availability']) {
                /* Begin Available Items (Holds) */
                $availabilityString .= '<span class="available text-success">';
                $availabilityString .= $this->view->transEsc("Available");
                $availabilityString .= '</span>';
                
                /* leihbar */
                if ($loan_availability) {

                }
                 /* lesesaalbenutzung */
                elseif ($presentation_availability) {
                    $availabilityString .= " " . $this->view->transEsc("holding_only_presence_use");
                }
                /* Fernleihe */
                elseif ($interloan_availability) {
                    $availabilityString .= " " . $this->view->transEsc("fernleihe");
                }

                /* bestellbar? */
                if ($item[order_link] != "") {
                    $availabilityString .= ' <a href="$holding[order_link]" target="_blank">';
                    $availabilityString .= $this->view->transEsc("holding_place");
                    $availabilityString .= '</a>';
                }
                /* Limitierung a la Kurzausleihe
                 * leider nicht praktikabel, da auch andere Limitierungen angezeigt werden
                 * */
                /*if ($item[item_notes]) {
                  $availabilityString .= " (" . $this->view->transEsc($item[item_notes]) . ")";
                }*/
            } elseif ($item[availability] == 0) {
                $availabilityString .= '<span class="checkedout text-danger">';
                $availabilityString .= $this->view->transEsc("Checked Out");
                $availabilityString .= '</span>';
                if (isset($item[duedate])) {
                    if ($item[duedate] != '01.01.1970') { /* bei Vormerkung über den OPAC wird kein Datum gesetzt */
                        $availabilityString .= " " . $this->view->transEsc("Due") . " ";
                        $availabilityString .= $this->view->transEsc($item[duedate]);
                    }
                    $availabilityString .= ' <a href="$item[recall_link]" target="_blank">';
                    $availabilityString .= $this->view->transEsc("Recall This");
                    $availabilityString .= '</a>';
                }
                if ($item[loan_queue] != "") {
                    $availabilityString .= $item[loan_queue];
                    $availabilityString .= $this->view->transEsc("holding_recalled");
                }
            } else {
                if ($item[interlibraryLoan] == "1") {
                    $availabilityString .= '<span><a href="http://gso.gbv.de/request/FORM/LOAN?PPN=$item[id]" target="_blank">';
                    $availabilityString .= $this->view->transEsc("interlibrary loan");
                    $availabilityString .= '</a></span>';
                } else {
                    $availabilityString .= $this->view->transEsc("holding_not_for_loan");
                }
            }
        }
        
        return $availabilityString;
    }
  
    public function getAvailability(&$itemRow)
    {
        // AJAX Check record?
        $check = isset($itemRow['check']) && $itemRow['check'];
        $checkStorageRetrievalRequest = isset($itemRow['checkStorageRetrievalRequest']) && $itemRow['checkStorageRetrievalRequest'];
        $checkILLRequest = isset($itemRow['checkILLRequest']) && $itemRow['checkILLRequest'];
        
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
                    if (isset($itemRow['link']) && $itemRow['link']) {
                        $availabilityString .= '<a class="' . ($check ? 'checkRequest ' : '') . 'placehold" data-lightbox href="' . $this->view->recordLink()->getRequestUrl($itemRow['link']) . '"><i class="fa fa-flag" aria-hidden="true"></i>&nbsp;' . $this->view->transEsc($check ? "Check Hold" : "Place a Hold") . '</a>';
                    }
                    if (isset($itemRow['storageRetrievalRequestLink']) && $itemRow['storageRetrievalRequestLink']) {
                        $availabilityString .= '<a class="' . ($checkStorageRetrievalRequest ? 'checkStorageRetrievalRequest ' : '') . 'placeStorageRetrievalRequest" data-lightbox href="' . $this->view->recordLink()->getRequestUrl($itemRow['storageRetrievalRequestLink']) . '"><i class="fa fa-flag" aria-hidden="true"></i>&nbsp;' . $this->view->transEsc($checkStorageRetrievalRequest ? "storage_retrieval_request_check_text" : "storage_retrieval_request_place_text") . '</a>';
                    }
                    if (isset($itemRow['ILLRequestLink']) && $itemRow['ILLRequestLink']) {
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
                    if (isset($itemRow['link']) && $itemRow['link']) {
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
    
    public function getLocation(&$holding)
    {
        $locationText = $this->view->transEsc('location_' . $holding['location'], [], $holding['location']);
        
        if (isset($holding['locationhref']) && $holding['locationhref']) {
            $locationText = '<a href="' . $holding['locationhref'] . '" target="_blank">' . $locationText . '</a>';
        }
        
        return $locationText;
    }
    
    public function getCallNumber(&$item)
    {
        return $item['callnumber'];
    }
    
    public function getHoldingComments($record = null, &$item)
    {
      /*
       * Exemplarbezogene Daten auslesen
       * 
       * Zwei Ansätze denkbar:
       * 1. aus 980$g Marc
       *  Vorteil: genauer
       *  Nachteil: u.U. nicht aktuell
       * 2. about Text der DAIA-response
       *  Vorteil: sofort sichtbar nach Änderung
       *  Nachteil: nicht immer nur Pica Feld 4802? ungenau
       * 
       * Vorbedingung:
       * 980 $2 == 31
       * 980 $b == epn
       * Ausgabe:
       * 980 $g
       * 980 $k und $l mit angeben?
       */
      

      list($txt, $epn) = explode(":", $item[item_id]);
      $retVal = [];

      /*
       * Variante 1
       */
      if (is_null($record)) {
        $record = $this->view->driver;
       }

      //$marcRecord = $record->getMarcRecord();    
/*
      $allComments = $record->getFieldArray('980', ['2', 'b', 'g', 'k', 'l'], false);

      foreach ($allComments as $aC) {
        if ($aC['2'] == $iln) {
          if ($aC['b'] == $epn) {
            $retVal[] = $aC['g'];
          }
        }
      }

      /*
       * Variante 2
       * leider wird item > about nicht ausgeliefert > implementiert in ILS/Drive/PAIA.php
       */

       if ($item['about']) {
           $retVal = array_merge($retVal, explode("\n", $item['about']));
       }
      
      return $retVal;
    }
    
    public function getCallNumbers($holding)
    {
        $callnumberString = '';
        
        $callNos = $this->view->tab->getUniqueCallNumbers($holding['items']);
        if (!empty($callNos)) {
            foreach ($callNos as $callNo) {
                if ($this->view->callnumberHandler) {
                    $callnumberString .= '<a href="' . $this->view->url('alphabrowse-home') . '?source=' . $this->view->escapeHtmlAttr($this->view->callnumberHandler) . '&amp;from=' . $this->view->escapeHtmlAttr($callNo) . '">' . $this->view->escapeHtml($callNo) . '</a>';
                } else {
                    $callnumberString .= $this->view->escapeHtml($callNo);
                }
                $callnumberString .= '<br />';
            }
        } else {
            $callnumberString = '&nbsp;';
        }
        
        return $callnumberString;
    }

}