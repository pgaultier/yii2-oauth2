<?php
/**
 * JwtModelInterface.php
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

/**
 * This is the jwt model interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since XXX
 *
 * @property string $id
 * @property string $clientId
 * @property string $subject
 * @property string $publicKey
 */
interface JwtModelInterface extends BaseModelInterface
{

    const HASH_ALGO = 'sha256';

    /**
     * Find one jwt by its key
     *
     * @param array|string $condition
     * @return JwtModelInterface|null
     * @since XXX
     * @throws \yii\base\UnknownClassException
     */
    public static function findOne($condition);

    /**
     * @param bool $runValidation
     * @param null $attributes
     * @return bool
     * @since XXX
     * @throws \yii\base\UnknownClassException
     */
    public function save($runValidation = true, $attributes = null);

    /**
     * @return bool
     * @since XXX
     * @throws \yii\base\UnknownClassException
     */
    public function delete();

    /**
     * @param string $clientId
     * @param string $subject
     * @return string jwt fingerprint
     * @since XXX
     */
    public static function getFingerprint($clientId, $subject);
}
