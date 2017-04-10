<?php
/**
 * Jti.php
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

use sweelix\oauth2\server\interfaces\JtiModelInterface;
use Yii;

/**
 * This is the jti model
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
 * @property string $audience
 * @property string $expires
 * @property string $jti
 */
class Jti extends BaseModel implements JtiModelInterface
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['clientId', 'subject', 'audience', 'jti'], 'string'],
            [['clientId', 'subject', 'audience', 'jti'], 'required'],
        ];
    }

    /**
     * @return \sweelix\oauth2\server\interfaces\JtiServiceInterface
     */
    protected static function getDataService()
    {
        return Yii::createObject('sweelix\oauth2\server\interfaces\JtiServiceInterface');
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
            'audience' => 'string',
            'expires' => 'string',
            'jti' => 'string',
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
            $audience = isset($condition['audience']) ? $condition['audience'] : '';
            $expires = isset($condition['expires']) ? $condition['expires'] : '';
            $jti = isset($condition['jti']) ? $condition['jti'] : '';
            $condition = self::getFingerprint($clientId, $subject, $audience, $expires, $jti);
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
        // regenerate jti id before saving data
        $this->id = self::getFingerprint($this->clientId, $this->subject, $this->audience, $this->expires, $this->jti);
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public static function getFingerprint($clientId, $subject, $audience, $expires, $jti)
    {
        return hash(self::HASH_ALGO, $clientId.':'.$subject.':'.$audience.':'.$expires.':'.$jti);
    }
}
