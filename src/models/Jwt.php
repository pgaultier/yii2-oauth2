<?php
/**
 * Jwt.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @since 1.0.0
 */

namespace sweelix\oauth2\server\models;

use sweelix\oauth2\server\interfaces\JwtModelInterface;
use Yii;

/**
 * This is the jwt model
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
 * @property string $clientId
 * @property string $subject
 * @property string $publicKey
 */
class Jwt extends BaseModel implements JwtModelInterface
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['clientId', 'subject', 'publicKey'], 'string'],
            [['clientId', 'subject', 'publicKey'], 'required'],
        ];
    }

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
     * @since 1.0.0
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
     * @inheritdoc
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
     * @return bool
     * @since 1.0.0
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
     * @since 1.0.0
     */
    public static function getFingerprint($clientId, $subject)
    {
        return hash(self::HASH_ALGO, $clientId.':'.$subject);
    }

}
