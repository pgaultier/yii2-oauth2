<?php
/**
 * Jwt.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @since XXX
 */

namespace sweelix\oauth2\server\models;

use Yii;

/**
 * This is the jwt model
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
 * @property string $clientId
 * @property string $subject
 * @property string $publicKey
 */
class Jwt extends BaseModel
{
    const HASH_ALGO = 'sha256';

    /**
     * @return \sweelix\oauth2\server\interfaces\JwtServiceInterface
     */
    protected static function getDataService()
    {
        return Yii::createObject('sweelix\oauth2\server\interfaces\JwtServiceInterface');
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
            'clientId' => 'string',
            'subject' => 'string',
            'publicKey' => 'string',
        ];
    }

    /**
     * Find one jwt by its key
     *
     * @param array|string $condition
     * @return Jwt|null
     * @since XXX
     * @throws \yii\base\UnknownClassException
     */
    public static function findOne($condition)
    {
        if (is_array($condition) === true) {
            $clientId = isset($condition['clientId']) ? $condition['clientId'] : '';
            $subject = isset($condition['subject']) ? $condition['subject'] : '';
            $condition = self::getFingerprint($clientId, $subject);
        }
        return self::getDataService()->findOne($condition);
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
        // regenerate jwt id before saving data
        $this->id = self::getFingerprint($this->clientId, $this->subject);
        return parent::beforeSave($insert);
    }

    /**
     * @param string $clientId
     * @param string $subject
     * @return string jwt fingerprint
     * @since XXX
     */
    public static function getFingerprint($clientId, $subject)
    {
        return hash(self::HASH_ALGO, $clientId.':'.$subject);
    }

}
