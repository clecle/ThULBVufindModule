<?php
/**
 * Permission Provider Factory Class
 *
 * @category ThULB
 * @package  Authorization
 * @author   Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
namespace ThULB\Role\PermissionProvider;
use Zend\ServiceManager\ServiceManager;

/**
 * Permission Provider Factory Class
 *
 * @category ThULB
 * @package  Authorization
 * @author   Richard Großer <richard.grosser@thulb.uni-jena.de>
 *
 * @codeCoverageIgnore
 */
class Factory
{
    /**
     * Factory for GetParam
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return GetParam
     */
    public static function getGetParam(ServiceManager $sm)
    {
        return new GetParam($sm->getServiceLocator()->get('Request'), $sm->getServiceLocator()->get('VuFind\CookieManager'));
    }
}
