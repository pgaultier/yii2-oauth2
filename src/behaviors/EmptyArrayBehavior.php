<?php
/**
 * EmptyArrayBehavior.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\behaviors
 */

namespace sweelix\oauth2\server\behaviors;

use sweelix\oauth2\server\models\BaseModel;
use yii\base\Behavior;

/**
 * Change attribute to empty array.
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\behaviors
 * @since 1.0.0
 */
class EmptyArrayBehavior extends Behavior
{
    /**
     * @var array list of attributes to update
     */
    public $attributes = [];
    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            BaseModel::EVENT_BEFORE_UPDATE => 'updateAttribute',
            BaseModel::EVENT_BEFORE_INSERT => 'updateAttribute',
        ];
    }

    /**
     * @since 1.0.0
     */
    public function updateAttribute()
    {
        foreach($this->attributes as $attribute) {
            if ($this->owner->{$attribute} === null) {
                $this->owner->{$attribute} = [];
            }
        }
    }

}
