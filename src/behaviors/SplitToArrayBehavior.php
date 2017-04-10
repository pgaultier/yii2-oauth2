<?php
/**
 * SplitToArrayBehavior.php
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
 * This behavior change one attribute to array by splitting the string.
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\behaviors
 * @since 1.0.0
 */
class SplitToArrayBehavior extends Behavior
{
    /**
     * @var array list of attributes to update
     */
    public $attributes = [];

    /**
     * @var string separator used to split the string
     */
    public $separator = ' ';

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
            if (empty($this->owner->{$attribute}) === true) {
                $this->owner->{$attribute} = [];
            } elseif (is_array($this->owner->{$attribute}) === false) {
                $this->owner->{$attribute} = explode($this->separator, $this->owner->{$attribute});
            }
        }
    }

}
