<?php
/**
 * Client.php
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

use sweelix\oauth2\server\behaviors\EmptyArrayBehavior;
use sweelix\oauth2\server\interfaces\ClientModelInterface;
use Yii;

/**
 * This is the client model
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
 * @property string $secret
 * @property string $redirectUri
 * @property array $grantTypes
 * @property string $userId
 * @property array $scopes
 * @property string $name
 * @property bool $isPublic
 */
class Client extends BaseModel implements ClientModelInterface
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['emptyArray'] = [
            'class' => EmptyArrayBehavior::className(),
            'attributes' => ['scopes', 'grantTypes'],
        ];
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'secret', 'userId', 'name'], 'string'],
            [['redirectUri'], 'url', 'when' => function($model) {
                $isLocalhost = strncmp('http://localhost', $model->redirectUri, 16);
                $isSecureLocalhost = strncmp('https://localhost', $model->redirectUri, 17);
                return (($isLocalhost !== 0) && ($isSecureLocalhost !== 0));
            }],
            [['scopes'], 'scope'],
            [['isPublic'], 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
            [['id', 'secret', 'isPublic'], 'required'],
        ];
    }

    /**
     * @return \sweelix\oauth2\server\interfaces\ClientServiceInterface
     */
    protected static function getDataService()
    {
        return Yii::createObject('sweelix\oauth2\server\interfaces\ClientServiceInterface');
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
            'secret' => 'string',
            'redirectUri' => 'string',
            'grantTypes' => 'array',
            'userId' => 'string',
            'scopes' => 'array',
            'name' => 'string',
            'isPublic' => 'bool',
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
    public function hasUser($userId)
    {
        return self::getDataService()->hasUser($this, $userId);
    }

    /**
     * @inheritdoc
     */
    public function addUser($userId)
    {
        return self::getDataService()->addUser($this, $userId);
    }

    public function removeUser($userId)
    {
        return self::getDataService()->removeUser($this, $userId);
    }

}
