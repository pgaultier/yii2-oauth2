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

use sweelix\oauth2\server\interfaces\AccessTokenModelInterface;
use Yii;

/**
 * This is the access token model
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
 * @property string $userId
 * @property string $expiry
 * @property array $scopes
 */
class AccessToken extends BaseModel implements AccessTokenModelInterface
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'clientId', 'userId'], 'string'],
            [['isDefault'], 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
            [['id', 'isDefault'], 'required'],
        ];
    }

    /**
     * @return \sweelix\oauth2\server\interfaces\AccessTokenServiceInterface
     */
    protected static function getDataService()
    {
        return Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenServiceInterface');
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

}
