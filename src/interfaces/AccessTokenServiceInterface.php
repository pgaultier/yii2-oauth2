<?php
/**
 * AccessTokenServiceInterface.php
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

use sweelix\oauth2\server\models\AccessToken;

/**
 * This is the access token service interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since XXX
 */
interface AccessTokenServiceInterface
{
    /**
     * Save or update Access Token depending on isNewRecord flag
     * @param AccessToken $accessToken
     * @param null|array $attributes attributes to save
     * @return boolean
     * @throws \Exception
     */
    public function save(AccessToken $accessToken, $attributes);

    /**
     * @param string $key
     * @return AccessToken|null
     */
    public function findOne($key);

    /**
     * @param AccessToken $accessToken
     * @return boolean
     */
    public function delete(AccessToken $accessToken);

}
