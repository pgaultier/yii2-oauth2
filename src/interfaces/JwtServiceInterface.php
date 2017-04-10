<?php
/**
 * JwtServiceInterface.php
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
 * This is the jwt service interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since 1.0.0
 */
interface JwtServiceInterface
{
    /**
     * Save or update jwt depending on isNewRecord flag
     * @param JwtModelInterface $jwt
     * @param null|array $attributes attributes to save
     * @return boolean
     * @throws \Exception
     */
    public function save(JwtModelInterface $jwt, $attributes);

    /**
     * @param string $key
     * @return JwtModelInterface|null
     */
    public function findOne($key);

    /**
     * @param JwtModelInterface $jwt
     * @return boolean
     */
    public function delete(JwtModelInterface $jwt);

}
