<?php
/**
 * CypherKeyModelInterface.php
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
 * This is the cypher key model interface
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
 * @property string $publicKey
 * @property string $privateKey
 * @property string $encryptionAlgorithm
 */
interface CypherKeyModelInterface extends BaseModelInterface
{
    const DEFAULT_KEY = 'default';

    /**
     * @string JwtAccessToken supported algos are hash_hmac HS256, HS384, HS512 or openssl_sign RS256, RS384, RS512
     */
    const HASH_ALGO = 'RS256';

    /**
     * Find one cypher key by its key
     *
     * @param string $id
     * @return CypherKeyModelInterface|null
     * @since XXX
     * @throws \yii\base\UnknownClassException
     */
    public static function findOne($id);

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
     * generate private and public keys
     * @since XXX
     */
    public function generateKeys($bits = 2048, $type = OPENSSL_KEYTYPE_RSA);
}
