<?php
/**
 * AccessTokenServiceInterface.php
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
 * This is the access token service interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since 1.0.0
 */
interface AccessTokenServiceInterface
{
    /**
     * Save or update Access Token depending on isNewRecord flag
     * @param AccessTokenModelInterface $accessToken
     * @param null|array $attributes attributes to save
     * @return boolean
     * @throws \Exception
     */
    public function save(AccessTokenModelInterface $accessToken, $attributes);

    /**
     * @param string $key
     * @return AccessTokenModelInterface|null
     */
    public function findOne($key);

    /**
     * @param AccessTokenModelInterface $accessToken
     * @return boolean
     */
    public function delete(AccessTokenModelInterface $accessToken);

    /**
     * @param string $userId
     * @return AccessTokenModelInterface[]
     */
    public function findAllByUserId($userId);

    /**
     * @param string $userId
     * @return bool
     */
    public function deleteAllByUserId($userId);

    /**
     * @param string $clientId
     * @return AccessTokenModelInterface[]
     */
    public function findAllByClientId($clientId);

    /**
     * @param string $clientId
     * @return bool
     */
    public function deleteAllByClientId($clientId);

}
