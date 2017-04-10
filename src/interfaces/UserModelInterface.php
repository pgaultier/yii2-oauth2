<?php
/**
 * UserModelInterface.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 */

namespace sweelix\oauth2\server\interfaces;

use OAuth2\Controller\ResourceController;
use yii\web\IdentityInterface;

/**
 * This is the user interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since 1.0.0
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
     * @return array list of restricted scopes for current user or null
     */
    public function getRestrictedScopes();

    /**
     * @param array $scopes define restricted scopes for current user
     */
    public function setRestrictedScopes($scopes);
}
