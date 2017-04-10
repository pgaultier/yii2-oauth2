<?php
/**
 * JtiServiceInterface.php
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
 * This is the jti service interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since 1.0.0
 */
interface JtiServiceInterface
{
    /**
     * Save or update jti depending on isNewRecord flag
     * @param JtiModelInterface $jti
     * @param null|array $attributes attributes to save
     * @return boolean
     * @throws \Exception
     */
    public function save(JtiModelInterface $jti, $attributes);

    /**
     * @param string $key
     * @return JtiModelInterface|null
     */
    public function findOne($key);

    /**
     * @param JtiModelInterface $jti
     * @return boolean
     */
    public function delete(JtiModelInterface $jti);

}
