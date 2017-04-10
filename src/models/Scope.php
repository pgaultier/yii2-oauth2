<?php
/**
 * Scope.php
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

use sweelix\oauth2\server\interfaces\ScopeModelInterface;
use Yii;

/**
 * This is the scope model
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
 * @property bool $isDefault
 * @property string $definition
 */
class Scope extends BaseModel implements ScopeModelInterface
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
     * @since 1.0.0
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
    public static function findAvailableScopeIds()
    {
        return self::getDataService()->findAvailableScopeIds();
    }

    /**
     * @inheritdoc
     */
    public static function findDefaultScopeIds($clientId = null)
    {
        return self::getDataService()->findDefaultScopeIds($clientId);
    }

}
