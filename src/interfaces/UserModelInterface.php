<?php
/**
 * UserModelInterface.php
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

use yii\web\IdentityInterface;

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
interface UserModelInterface extends IdentityInterface
{
    /**
     * @param string $username
     * @param string $password
     * @return UserModelInterface
     */
    public static function findByUsernameAndPassword($username, $password);

    /**
     * @param string $username
     * @return UserModelInterface
     */
    public static function findByUsername($username);

    /**
     * @return array list of scopes for current user
     * @since XXX
     */
    public function getScopes();
}
