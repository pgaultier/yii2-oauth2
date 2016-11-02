<?php
/**
 * MockUser.php
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 */

namespace tests\unit;

use sweelix\oauth2\server\interfaces\UserModelInterface;
use sweelix\oauth2\server\traits\IdentityTrait;
use yii\base\NotSupportedException;

class MockUser implements UserModelInterface
{

    use IdentityTrait;

    public static $users = [
        'user1' => [
            'id' => 'user1',
            'password' => 'password1',
            'scopes' => ['basic']
        ],
        'user2' => [
            'id' => 'user2',
            'password' => 'password2',
            'scopes' => []
        ],
    ];
    public $id;
    public $username;
    public $password;

    public function getId()
    {
        return $this->id;
    }

    public static function findByUsername($username)
    {
        $user = null;
        if (isset(self::$users[$username]) === true) {
            $user = new self();
            $user->id = self::$users[$username]['id'];
            $user->username = $username;
            $user->password = self::$users[$username]['password'];
        }
        return $user;
    }

    public static function findByUsernameAndPassword($username, $password)
    {
        $user = null;
        if ((isset(self::$users[$username]) === true) && (self::$users[$username]['password'] === $password)) {
            $user = new self();
            $user->id = self::$users[$username]['id'];
            $user->username = $username;
            $user->password = self::$users[$username]['password'];
        }
        return $user;
    }

    public static function findIdentity($id)
    {
        throw new NotSupportedException();
    }

    public function validateAuthKey($authKey)
    {
        throw new NotSupportedException();
    }

    public function getAuthKey()
    {
        throw new NotSupportedException();
    }
}