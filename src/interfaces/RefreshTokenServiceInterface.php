<?php
/**
 * RefreshTokenServiceInterface.php
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

use sweelix\oauth2\server\models\RefreshToken;

/**
 * This is the refresh token service interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since XXX
 */
interface RefreshTokenServiceInterface
{
    /**
     * Save or update Refresh Token depending on isNewRecord flag
     * @param RefreshToken $refreshToken
     * @param null|array $attributes attributes to save
     * @return boolean
     * @throws \Exception
     */
    public function save(RefreshToken $refreshToken, $attributes);

    /**
     * @param string $key
     * @return RefreshToken|null
     */
    public function findOne($key);

    /**
     * @param RefreshToken $refreshToken
     * @return boolean
     */
    public function delete(RefreshToken $refreshToken);

}
