<?php
/**
 * AfterSaveEvent.php
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

use yii\base\Event;

/**
 * AfterSaveEvent represents the information available in [[Model::EVENT_AFTER_INSERT]] and [[Model::EVENT_AFTER_UPDATE]].
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package modules\v1\models
 * @since 1.0.0
 */
class AfterSaveEvent extends Event
{
    /**
     * @var array The attribute values that had changed and were saved.
     */
    public $changedAttributes;
}
