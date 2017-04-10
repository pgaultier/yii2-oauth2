<?php
/**
 * AuthCodeServiceInterface.php
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
 * This is the auth code service interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since 1.0.0
 */
interface AuthCodeServiceInterface
{
    /**
     * Save or update auth code depending on isNewRecord flag
     * @param AuthCodeModelInterface $authCode
     * @param null|array $attributes attributes to save
     * @return boolean
     * @throws \Exception
     */
    public function save(AuthCodeModelInterface $authCode, $attributes);

    /**
     * @param string $key
     * @return AuthCodeModelInterface|null
     */
    public function findOne($key);

    /**
     * @param AuthCodeModelInterface $authCode
     * @return boolean
     */
    public function delete(AuthCodeModelInterface $authCode);

}
