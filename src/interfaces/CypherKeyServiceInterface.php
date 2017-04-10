<?php
/**
 * CypherKeyServiceInterface.php
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

use sweelix\oauth2\server\models\CypherKey;

/**
 * This is the cypher key service interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since 1.0.0
 */
interface CypherKeyServiceInterface
{
    /**
     * Save or update Cypher Key depending on isNewRecord flag
     * @param CypherKey $accessToken
     * @param null|array $attributes attributes to save
     * @return boolean
     * @throws \Exception
     */
    public function save(CypherKey $accessToken, $attributes);

    /**
     * @param string $key
     * @return CypherKey|null
     */
    public function findOne($key);

    /**
     * @param CypherKey $accessToken
     * @return boolean
     */
    public function delete(CypherKey $accessToken);

}
