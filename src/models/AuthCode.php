<?php
/**
 * AuthCode.php
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
 * This is the auth code model
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
 * @property string $redirectUri
 * @property string $expiry
 * @property array $scopes
 * @property string $tokenId
 */
class AuthCode extends BaseModel
{

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
     * @since XXX
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
     * Find one scope by its key
     *
     * @param string $id
     * @return AuthCode|null
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

}
