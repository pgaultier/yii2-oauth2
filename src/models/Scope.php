<?php
/**
 * Scope.php
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
 * This is the scope model
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
 * @property bool $isDefault
 * @property string $definition
 */
class Scope extends BaseModel
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'definition'], 'string'],
            [['isDefault'], 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
            [['id', 'isDefault'], 'required'],
        ];
    }

    /**
     * @return \sweelix\oauth2\server\interfaces\ScopeServiceInterface
     */
    protected static function getDataService()
    {
        return Yii::createObject('sweelix\oauth2\server\interfaces\ScopeServiceInterface');
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
            'isDefault' => 'bool',
            'definition' => 'string',
        ];
    }

    /**
     * Find one scope by its key
     *
     * @param string $id
     * @return Scope|null
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

    /**
     * @return array list of scope IDs
     * @since XXX
     */
    public static function findAvailableScopeIds()
    {
        return self::getDataService()->findAvailableScopeIds();
    }

    /**
     * @param string $clientId
     * @return array default scope for selected client Id
     * @since XXX
     */
    public static function findDefaultScopeIds($clientId = null)
    {
        return self::getDataService()->findDefaultScopeIds($clientId);
    }

}
