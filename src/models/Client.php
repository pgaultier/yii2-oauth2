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
 * @since XXX
 */

namespace sweelix\oauth2\server\models;

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
class Client extends BaseModel
{
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
     * Find one client by its key
     *
     * @param string $id
     * @return Client|null
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
