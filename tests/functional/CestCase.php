<?php
/**
 * CestCase.php
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

namespace tests\functional;

use Yii;

class CestCase
{
    /**
     * Clean everything from database
     */
    protected function cleanDatabase()
    {
        $keys = Yii::$app->redis->executeCommand('KEYS', ['oauth2:*']);
        if (empty($keys) === false) {
            Yii::$app->redis->executeCommand('DEL', $keys);
        }
    }

}
