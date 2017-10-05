<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ThULB\View\Helper\Root;

use Zend\View\Helper\AbstractHelper;

/**
 * Description of ServerType
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class ServerType extends AbstractHelper {
    /**
     * Get the VuFind environment variable value.
     *
     * @return String
     */
    public function __invoke()
    {
        return $_SERVER['VUFIND_ENV'];
    }
}
