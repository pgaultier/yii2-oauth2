<?php
/**
 * JtiModelInterface.php
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
 * This is the jti model interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since 1.0.0
 *
 * @property string $id
 * @property string $clientId
 * @property string $subject
 * @property string $audience
 * @property string $expires
 * @property string $jti
 */
interface JtiModelInterface extends BaseModelInterface
{

    const HASH_ALGO = 'sha256';

    /**
     * Find one jti by its key
     *
     * @param array|string $condition
     * @return JtiModelInterface|null
     * @since 1.0.0
     * @throws \yii\base\UnknownClassException
     */
    public static function findOne($condition);

    /**
     * @param bool $runValidation
     * @param null $attributes
     * @return bool
     * @since 1.0.0
     * @throws \yii\base\UnknownClassException
     */
    public function save($runValidation = true, $attributes = null);

    /**
     * @return bool
     * @since 1.0.0
     * @throws \yii\base\UnknownClassException
     */
    public function delete();

    /**
     * @param string|integer $subject
     * @return JtiModelInterface[]
     * @since XXX
     * @throws \yii\base\UnknownClassException
     */
    public static function findAllBySubject($subject);

    /**
     * @param string|integer $subject
     * @return bool
     * @since XXX
     * @throws \yii\base\UnknownClassException
     */
    public static function deleteAllBySubject($subject);

    /**
     * @param string $clientId
     * @return JtiModelInterface[]
     * @since XXX
     * @throws \yii\base\UnknownClassException
     */
    public static function findAllByClientId($clientId);

    /**
     * @param string $clientId
     * @return bool
     * @since XXX
     * @throws \yii\base\UnknownClassException
     */
    public static function deleteAllByClientId($clientId);

    /**
     * @return bool
     * @throws \yii\base\UnknownClassException
     */
    public static function deleteAllExpired();

    /**
     * @return JtiModelInterface[]
     * @throws \yii\base\UnknownClassException
     */
    public static function findAll();

    /**
     * @param string $clientId
     * @param string $subject
     * @param string $audience
     * @param string $expires
     * @param $jti
     * @return string jti fingerprint
     * @since 1.0.0
     */
    public static function getFingerprint($clientId, $subject, $audience, $expires, $jti);
}
