<?php
/**
 * RefreshToken.php
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

use sweelix\oauth2\server\behaviors\EmptyArrayBehavior;
use sweelix\oauth2\server\interfaces\RefreshTokenModelInterface;
use Yii;

/**
 * This is the refresh token model
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
 * @property string $userId
 * @property string $expiry
 * @property array $scopes
 */
class RefreshToken extends BaseModel implements RefreshTokenModelInterface
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['emptyArray'] = [
            'class' => EmptyArrayBehavior::className(),
            'attributes' => ['scopes'],
        ];
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'clientId'], 'string'],
            [['scopes'], 'scope'],
        ];
    }

    /**
     * @return \sweelix\oauth2\server\interfaces\RefreshTokenServiceInterface
     */
    protected static function getDataService()
    {
        return Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenServiceInterface');
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
            'userId' => 'string',
            'expiry' => 'string',
            'scopes' => 'array',
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
    public static function findAllByUserId($userId)
    {
        return self::getDataService()->findAllByUserId($userId);
    }

    /**
     * @inheritdoc
     */
    public static function deleteAllByUserId($userId)
    {
        return self::getDataService()->deleteAllByUserId($userId);
    }

    /**
     * @inheritdoc
     */
    public static function findAllByClientId($clientId)
    {
        return self::getDataService()->findAllByClientId($clientId);
    }

    /**
     * @inheritdoc
     */
    public static function deleteAllByClientId($clientId)
    {
        return self::getDataService()->deleteAllByClientId($clientId);
    }

}
