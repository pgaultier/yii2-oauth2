<?php
/**
 * JwtServiceInterface.php
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

use sweelix\oauth2\server\models\Jwt;

/**
 * This is the jwt service interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since XXX
 */
interface JwtServiceInterface
{
    /**
     * Save or update jwt depending on isNewRecord flag
     * @param Jwt $jwt
     * @param null|array $attributes attributes to save
     * @return boolean
     * @throws \Exception
     */
    public function save(Jwt $jwt, $attributes);

    /**
     * @param string $key
     * @return Jwt|null
     */
    public function findOne($key);

    /**
     * @param Jwt $jwt
     * @return boolean
     */
    public function delete(Jwt $jwt);

}
