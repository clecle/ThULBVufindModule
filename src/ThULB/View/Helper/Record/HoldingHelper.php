<?php

namespace ThULB\View\Helper\Record;

use Zend\View\Helper\AbstractHelper;

class HoldingHelper extends AbstractHelper
{

    public function blabberblubb($text)
    {
      return 'holdings: ' . $text;
    }

}