<?php
/**
 * ClientServiceInterface.php
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
 * This is the client service interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since 1.0.0
 */
interface ClientServiceInterface
{
    /**
     * Save or update client depending on isNewRecord flag
     * @param ClientModelInterface $client
     * @param null|array $attributes attributes to save
     * @return boolean
     * @throws \Exception
     */
    public function save(ClientModelInterface $client, $attributes);

    /**
     * @param string $key
     * @return ClientModelInterface|null
     */
    public function findOne($key);

    /**
     * @param ClientModelInterface $client
     * @return boolean
     */
    public function delete(ClientModelInterface $client);

    /**
     * @param ClientModelInterface $client
     * @param string $userId
     * @return bool
     */
    public function hasUser(ClientModelInterface $client, $userId);

    /**
     * @param ClientModelInterface $client
     * @param string $userId
     * @return bool
     */
    public function addUser(ClientModelInterface $client, $userId);

    /**
     * @param ClientModelInterface $client
     * @param string $userId
     * @return bool
     */
    public function removeUser(ClientModelInterface $client, $userId);

    /**
     * @param string $userId
     * @return ClientModelInterface[]
     */
    public function findAllByUserId($userId);
}
