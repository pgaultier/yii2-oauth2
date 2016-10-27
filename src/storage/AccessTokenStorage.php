<?php
/**
 * AccessTokenStorage.php
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 */

namespace sweelix\oauth2\server\storage;

use OAuth2\Storage\AccessTokenInterface;
use OAuth2\Storage\JwtAccessTokenInterface;
use Yii;

class AccessTokenStorage implements AccessTokenInterface, JwtAccessTokenInterface
{
    /**
     * @var string
     */
    private $accessTokenClass;

    /**
     * @return string classname for selected interface
     * @since XXX
     */
    public function getAccessTokenClass()
    {
        if ($this->accessTokenClass === null) {
            $client = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
            $this->accessTokenClass = get_class($client);
        }
        return $this->accessTokenClass;
    }

    /**
     * @inheritdoc
     */
    public function getAccessToken($oauth_token)
    {
        $accessTokenClass = $this->getAccessTokenClass();
        $accessToken = $accessTokenClass::findOne($oauth_token);
        /* @var \sweelix\oauth2\server\interfaces\AccessTokenModelInterface $accessToken */
        if ($accessToken !== null) {
            $finalToken = [
                'expires' => $accessToken->expiry,
                'client_id' => $accessToken->clientId,
                'user_id' => $accessToken->userId,
                'scope' => implode(' ', $accessToken->scopes),
                'id_token' => $accessToken->id,
            ];
            $accessToken = $finalToken;
        }
        return $accessToken;
    }

    /**
     * @inheritdoc
     */
    public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = null)
    {
        $accessToken = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\AccessTokenModelInterface $accessToken */
        $accessToken->id = $oauth_token;
        $accessToken->clientId = $client_id;
        $accessToken->userId = $user_id;
        $accessToken->expiry = $expires;
        if ($scope === null) {
            $scopes = [];
        } else {
            $scopes = explode(' ', $scope);
        }
        $accessToken->scopes = $scopes;
        $accessToken->save();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function unsetAccessToken($access_token)
    {
        $accessTokenClass = $this->getAccessTokenClass();
        $accessToken = $accessTokenClass::findOne($access_token);
        /* @var \sweelix\oauth2\server\interfaces\AccessTokenModelInterface $accessToken */
        if ($accessToken !== null) {
            $accessToken->delete();
        }
        return true; //TODO: check why we should return true/false
    }
}