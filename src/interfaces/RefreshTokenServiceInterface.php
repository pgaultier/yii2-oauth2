<?php
/**
 * RefreshTokenServiceInterface.php
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

/**
 * This is the refresh token service interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since 1.0.0
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

    /**
     * @param string $userId
     * @return RefreshTokenModelInterface[]
     */
    public function findAllByUserId($userId);

    /**
     * @param string $userId
     * @return bool
     */
    public function deleteAllByUserId($userId);

    /**
     * @param string $clientId
     * @return RefreshTokenModelInterface[]
     */
    public function findAllByClientId($clientId);

    /**
     * @param string $clientId
     * @return bool
     */
    public function deleteAllByClientId($clientId);

}
