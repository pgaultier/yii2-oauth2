<?php
/**
 * AccessToken.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\models
 */

namespace sweelix\oauth2\server\models;

use sweelix\oauth2\server\interfaces\CypherKeyModelInterface;
use Yii;

/**
 * This is the cypher key model
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\models
 * @since 1.0.0
 *
 * @property string $id
 * @property string $publicKey
 * @property string $privateKey
 * @property string $encryptionAlgorithm
 */
class CypherKey extends BaseModel implements CypherKeyModelInterface
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'publicKey', 'privateKey'], 'string'],
            [['id', 'publicKey', 'privateKey'], 'required'],
        ];
    }

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
     * @since 1.0.0
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
     * @inheritdoc
     */
    public static function findOne($id)
    {
        return self::getDataService()->findOne($id);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
        if ($this->encryptionAlgorithm === null) {
            $this->encryptionAlgorithm = self::HASH_ALGO;
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function generateKeys($bits = 2048, $type = OPENSSL_KEYTYPE_RSA)
    {
        $opensslHandle = openssl_pkey_new([
            'private_key_bits' => $bits,
            'private_key_type' => $type
        ]);

        openssl_pkey_export($opensslHandle, $privateKey);
        $details = openssl_pkey_get_details($opensslHandle);
        $publicKey = $details['key'];
        openssl_free_key($opensslHandle);
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }
}
