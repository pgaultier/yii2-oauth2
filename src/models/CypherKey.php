<?php
/**
 * AccessToken.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\models
 */

namespace sweelix\oauth2\server\models;

use Yii;

/**
 * This is the cypher key model
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\models
 * @since XXX
 *
 * @property string $id
 * @property string $publicKey
 * @property string $privateKey
 * @property string $encryptionAlgoritm
 */
class CypherKey extends BaseModel
{
    const DEFAULT_KEY = 'default';

    /**
     * @string JwtAccessToken supported algos are hash_hmac HS256, HS384, HS512 or openssl_sign RS256, RS384, RS512
     */
    const HASH_ALGO = 'RS256';

    /**
     * @return \sweelix\oauth2\server\interfaces\CypherKeyServiceInterface
     */
    protected static function getDataService()
    {
        return Yii::createObject('sweelix\oauth2\server\interfaces\CypherKeyServiceInterface');
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return 'id';
    }

    /**
     * @return array definition of model attributes
     * @since XXX
     */
    public function attributesDefinition()
    {
        return [
            'id' => 'string',
            'publicKey' => 'string',
            'privateKey' => 'string',
            'encryptionAlgorithm' => 'string',
        ];
    }

    /**
     * Find one accessToken by its key
     *
     * @param string $id
     * @return CypherKey|null
     * @since XXX
     * @throws \yii\base\UnknownClassException
     */
    public static function findOne($id)
    {
        return self::getDataService()->findOne($id);
    }

    /**
     * @param bool $runValidation
     * @param null $attributes
     * @return bool
     * @since XXX
     * @throws \yii\base\UnknownClassException
     */
    public function save($runValidation = true, $attributes = null)
    {
        if ($runValidation && !$this->validate($attributes)) {
            Yii::info('Model not inserted due to validation error.', __METHOD__);
            $result = false;
        } else {
            $result = self::getDataService()->save($this, $attributes);
        }
        return $result;
    }

    /**
     * @return bool
     * @since XXX
     * @throws \yii\base\UnknownClassException
     */
    public function delete()
    {
        return self::getDataService()->delete($this);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->encryptionAlgoritm === null) {
            $this->encryptionAlgoritm = self::HASH_ALGO;
        }
        return parent::beforeSave($insert);
    }

    /**
     * @return array containing generated public and private keys ['public' => 'xxx', 'private' => 'yyy']
     * @since XXX
     */
    public static function generateKeys()
    {
        $opensslHandle = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ]);

        openssl_pkey_export($opensslHandle, $privateKey);
        $details = openssl_pkey_get_details($opensslHandle);
        $publicKey = $details['key'];
        openssl_free_key($opensslHandle);
        return [
            'public' => $publicKey,
            'private' => $privateKey,
        ];
    }
}
