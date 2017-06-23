<?php

namespace ThULB\Db\Table;
use Zend\ServiceManager\ServiceManager,
        VuFind\Db\Table\Factory as OriginalFactory,
        VuFind\Db\Table\User as UserTable;

/**
 * Factory for DB tables.
 *
 * @category ThULB
 * @package  Db_Table
 * @author   Richard Großer <richard.grosser@thulb.uni-jena.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 *
 * @codeCoverageIgnore
 */
class Factory extends OriginalFactory
{
    /**
     * Construct the User table.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return User
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
        $session = null;
        if ($privacy) {
            $sessionManager = $sm->getServiceLocator()->get('VuFind\SessionManager');
            $session = new \Zend\Session\Container('Account', $sessionManager);
        }
        return new UserTable($config, $rowClass, $session);
    }
}
