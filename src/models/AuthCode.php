<?php
/**
 * AuthCode.php
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
use sweelix\oauth2\server\interfaces\AuthCodeModelInterface;
use Yii;

/**
 * This is the auth code model
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
 * @property string $redirectUri
 * @property string $expiry
 * @property array $scopes
 * @property string $tokenId
 */
class AuthCode extends BaseModel implements AuthCodeModelInterface
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
            [['id', 'clientId', 'tokenId'], 'string'],
            [['redirectUri'], 'url', 'when' => function($model) {
                $isLocalhost = strncmp('http://localhost', $model->redirectUri, 16);
                $isSecureLocalhost = strncmp('https://localhost', $model->redirectUri, 17);
                return (($isLocalhost !== 0) && ($isSecureLocalhost !== 0));
            }],
            [['scopes'], 'scope'],
            [['id'], 'required'],
        ];
    }

    /**
     * @return \sweelix\oauth2\server\interfaces\AuthCodeServiceInterface
     */
    protected static function getDataService()
    {
        return Yii::createObject('sweelix\oauth2\server\interfaces\AuthCodeServiceInterface');
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
            'redirectUri' => 'string',
            'expiry' => 'string',
            'scopes' => 'array',
            'tokenId' => 'string',
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

}
