<?php
/**
 * AuthCodeServiceInterface.php
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

use sweelix\oauth2\server\models\AuthCode;

/**
 * This is the auth code service interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since XXX
 */
interface AuthCodeServiceInterface
{
    /**
     * Save or update auth code depending on isNewRecord flag
     * @param AuthCode $authCode
     * @param null|array $attributes attributes to save
     * @return boolean
     * @throws \Exception
     */
    public function save(AuthCode $authCode, $attributes);

    /**
     * @param string $key
     * @return AuthCode|null
     */
    public function findOne($key);

    /**
     * @param AuthCode $authCode
     * @return boolean
     */
    public function delete(AuthCode $authCode);

}
