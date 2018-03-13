<?php
/**
 * Factory methods for custom database table row classes
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2015.
 * Copyright (C) Thüringer Universitäts- und Landesbibliothek (ThULB) Jena, 2018.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category ThULB
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 */

namespace ThULB\Db\Row;
use Zend\ServiceManager\ServiceManager,
    VuFind\Db\Row\Factory as OriginalFactory;

/**
 * Description of Factory
 *
 * @author Richard Großer <richard.grosser@thulb.uni-jena.de>
 */
class Factory extends OriginalFactory
{
    /**
     * Construct the User row prototype.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return OAuthUser
     */
    public static function getUser(ServiceManager $sm)
    {
        $config = $sm->getServiceLocator()->get('VuFind\Config')->get('config');
        // Use a special row class when we're in privacy or oauth mode:
        $privacy = isset($config->Authentication->privacy)
            && $config->Authentication->privacy;
        $oauth = isset($config->Authentication->oauth)
            && $config->Authentication->oauth;
        $rowClass = $oauth ? 'ThULB\Db\Row\OAuthUser' : ('VuFind\Db\Row\\' . ($privacy ? 'PrivateUser' : 'User'));
        $prototype = static::getGenericRow($rowClass, $sm);
        $prototype->setConfig($config);
        if ($privacy) {
            $sessionManager = $sm->getServiceLocator()->get('VuFind\SessionManager');
            $session = new \Zend\Session\Container('Account', $sessionManager);
            $prototype->setSession($session);
        }
        return $prototype;
    }
}
