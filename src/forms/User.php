<?php
/**
 * User.php
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package sweelix\oauth2\server\forms
 */

namespace sweelix\oauth2\server\forms;

use yii\base\Model;

/**
 * Basic user form
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package sweelix\oauth2\server\forms
 * @since XXX
 */
class User extends Model
{
    public $username;
    public $password;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'string'],
            [['username', 'password'], 'required'],
        ];
    }
}