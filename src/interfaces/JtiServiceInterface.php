<?php
/**
 * JtiServiceInterface.php
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

use sweelix\oauth2\server\models\Jti;

/**
 * This is the jti service interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since XXX
 */
interface JtiServiceInterface
{
    /**
     * Save or update jti depending on isNewRecord flag
     * @param Jti $jti
     * @param null|array $attributes attributes to save
     * @return boolean
     * @throws \Exception
     */
    public function save(Jti $jti, $attributes);

    /**
     * @param string $issuer
     * @param string $subject
     * @param string $audience
     * @param string $expires
     * @param string $jti
     * @return Jti|null
     */
    public function findOne($issuer, $subject, $audience, $expires, $jti);

    /**
     * @param Jti $jti
     * @return boolean
     */
    public function delete(Jti $jti);

}
