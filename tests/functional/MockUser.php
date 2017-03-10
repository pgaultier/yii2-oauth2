<?php
/**
 * User.php
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

use sweelix\oauth2\server\interfaces\UserModelInterface;
use sweelix\oauth2\server\traits\IdentityTrait;
use Yii;

class MockUser implements UserModelInterface
{
    use IdentityTrait;

    public static $users = [
        'user1' => [
            'id' => 'userid1',
            'password' => 'password1',
            'scopes' => ['basic']
        ],
        'user2' => [
            'id' => 'userid2',
            'password' => 'password2',
            'scopes' => []
        ],
    ];
    public $id;
    public $username;
    public $password;
    private $authKey = 'demoauthkey';

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
            // $user->scopes = self::$users[$username]['scopes'];
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
            // $user->scopes = self::$users[$username]['scopes'];
        }
        return $user;
    }

    public static function findIdentity($id)
    {
        $user = null;
        foreach(self::$users as $username => $userData) {
            if ($userData['id'] === $id) {
                $user = new self();
                $user->id = $userData['id'];
                $user->username = $username;
                $user->password = $userData['password'];
                break;
            }
        }
        return $user;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function getAuthKey()
    {
        return $this->authKey;
    }
}
