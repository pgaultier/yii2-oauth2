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
     * @param CypherKeyModelInterface $cypherKey
     * @param null|array $attributes attributes to save
     * @return boolean
     * @throws \Exception
     */
    public function save(CypherKeyModelInterface $cypherKey, $attributes);

    /**
     * @param string $key
     * @return CypherKeyModelInterface|null
     * @throws \Exception
     */
    public function findOne($key);

    /**
     * @param CypherKeyModelInterface $cypherKey
     * @return boolean
     * @throws \Exception
     */
    public function delete(CypherKeyModelInterface $cypherKey);

}
