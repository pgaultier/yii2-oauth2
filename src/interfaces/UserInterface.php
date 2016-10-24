<?php
/**
 * UserInterface.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 */

namespace sweelix\oauth2\server\interfaces;

use League\OAuth2\Server\Entities\UserEntityInterface;
use sweelix\oauth2\server\models\Client;

/**
 * This is the user interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since XXX
 */
interface UserInterface extends UserEntityInterface
{
    /**
     * @param string $username
     * @param string $password
     * @param string $grantType
     * @param Client $client
     * @return UserInterface
     */
    public static function findByUsernameAndPassword($username, $password, $grantType = null, Client $client = null);
}
