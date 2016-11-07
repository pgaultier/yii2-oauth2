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
     * @param RefreshTokenModelInterface $refreshToken
     * @param null|array $attributes attributes to save
     * @return boolean
     * @throws \Exception
     */
    public function save(RefreshTokenModelInterface $refreshToken, $attributes);

    /**
     * @param string $key
     * @return RefreshTokenModelInterface|null
     */
    public function findOne($key);

    /**
     * @param RefreshTokenModelInterface $refreshToken
     * @return boolean
     */
    public function delete(RefreshTokenModelInterface $refreshToken);

}
