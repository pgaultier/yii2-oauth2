<?php
/**
 * Client.php
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
use sweelix\oauth2\server\behaviors\SplitToArrayBehavior;
use sweelix\oauth2\server\interfaces\ClientModelInterface;
use Yii;
use yii\validators\UrlValidator;

/**
 * This is the client model
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
 * @property string $secret
 * @property string|array $redirectUri
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
        $behaviors['splitToArray'] = [
            'class' => SplitToArrayBehavior::className(),
            'attributes' => ['redirectUri'],
        ];
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'secret', 'name'], 'string'],
            [['redirectUri'], function($attribute, $params) {
                $data = $this->{$attribute};

                if (is_array($data) === false) {
                    $data = explode(' ', $data);
                }
                foreach($data as $redirectUri) {
                    $isLocalhost = strncmp('http://localhost', $redirectUri, 16);
                    $isSecureLocalhost = strncmp('https://localhost', $redirectUri, 17);
                    if (($isLocalhost !== 0) && ($isSecureLocalhost !== 0)) {
                        $validator = new UrlValidator();
                        if ($validator->validate($redirectUri, $error) === false) {
                            $this->addError($attribute, $error);
                            break;
                        }
                    }
                }
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
     * @since 1.0.0
     */
    public function attributesDefinition()
    {
        return [
            'id' => 'string',
            'secret' => 'string',
            'redirectUri' => 'array',
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

    /**
     * @inheritdoc
     */
    public function removeUser($userId)
    {
        return self::getDataService()->removeUser($this, $userId);
    }

    /**
     * @inheritdoc
     */
    public static function findAllByUserId($userId)
    {
        return self::getDataService()->findAllByUserId($userId);
    }

}
