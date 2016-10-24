<?php
/**
 * ClientServiceInterface.php
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

use sweelix\oauth2\server\models\Client;

/**
 * This is the client service interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since XXX
 */
interface ClientServiceInterface
{
    /**
     * Save or update client depending on isNewRecord flag
     * @param Client $client
     * @param null|array $attributes attributes to save
     * @return boolean
     * @throws \Exception
     */
    public function save(Client $client, $attributes);

    /**
     * @param string $key
     * @return Client|null
     */
    public function findOne($key);

    /**
     * @param Client $client
     * @return boolean
     */
    public function delete(Client $client);

}
