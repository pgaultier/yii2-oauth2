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
     * @throws \Exception
     */
    public function findOne($key);

    /**
     * @param RefreshTokenModelInterface $refreshToken
     * @return boolean
     * @throws \Exception
     */
    public function delete(RefreshTokenModelInterface $refreshToken);

    /**
     * @param string $userId
     * @return RefreshTokenModelInterface[]
     * @throws \Exception
     */
    public function findAllByUserId($userId);

    /**
     * @param string $userId
     * @return bool
     * @throws \Exception
     */
    public function deleteAllByUserId($userId);

    /**
     * @param string $clientId
     * @return RefreshTokenModelInterface[]
     * @throws \Exception
     */
    public function findAllByClientId($clientId);

    /**
     * @param string $clientId
     * @return bool
     * @throws \Exception
     */
    public function deleteAllByClientId($clientId);

    /**
     * @return bool
     * @throws \Exception
     */
    public function deleteAllExpired();

    /**
     * @return RefreshTokenModelInterface[]
     * @throws \Exception
     */
    public function findAll();
}
