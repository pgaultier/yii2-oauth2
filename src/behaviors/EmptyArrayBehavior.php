<?php
/**
 * EmptyArrayBehavior.php
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package sweelix\oauth2\server\behaviors
 */

namespace sweelix\oauth2\server\behaviors;

use sweelix\oauth2\server\models\BaseModel;
use yii\base\Behavior;

/**
 * This exception is raised when a duplicate index is found and
 * entity cannot be inserted.
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\behaviors
 * @since XXX
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
     * @since XXX
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
