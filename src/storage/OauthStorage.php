<?php
/**
 * OauthStorage.php
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @packages sweelix\oauth2\server\storage
 */

namespace sweelix\oauth2\server\storage;

use OAuth2\OpenID\Storage\AuthorizationCodeInterface;
use OAuth2\Storage\AccessTokenInterface;
use OAuth2\Storage\ClientCredentialsInterface;
use OAuth2\Storage\JwtAccessTokenInterface;
use OAuth2\Storage\JwtBearerInterface;
use OAuth2\Storage\PublicKeyInterface;
use OAuth2\Storage\RefreshTokenInterface;
use OAuth2\Storage\ScopeInterface;
use OAuth2\Storage\UserCredentialsInterface;
use Yii;

/**
 * OauthStorage class
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @packages sweelix\oauth2\server\storage
 * @since 1.0.0
 */
class OauthStorage implements
    AccessTokenInterface,
    AuthorizationCodeInterface,
    ClientCredentialsInterface,
    JwtAccessTokenInterface, // identical to AccessTokenInterface
    JwtBearerInterface,
    PublicKeyInterface,
    RefreshTokenInterface,
    ScopeInterface,
    UserCredentialsInterface
{
    /**
     * @var string
     */
    private $accessTokenClass;

    /**
     * @var string
     */
    private $authCodeClass;

    /**
     * @var string
     */
    private $clientClass;

    /**
     * @var string
     */
    private $cypherKeyClass;

    /**
     * @var string
     */
    private $jtiClass;

    /**
     * @var string
     */
    private $jwtClass;

    /**
     * @var string
     */
    private $refreshTokenClass;

    /**
     * @var string
     */
    private $scopeClass;

    /**
     * @var string
     */
    private $userClass;

    /**
     * @return string classname for selected interface
     * @since 1.0.0
     */
    protected function getAccessTokenClass()
    {
        if ($this->accessTokenClass === null) {
            $accessToken = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
            $this->accessTokenClass = get_class($accessToken);
        }
        return $this->accessTokenClass;
    }

    /**
     * @return string classname for selected interface
     * @since 1.0.0
     */
    protected function getAuthCodeClass()
    {
        if ($this->authCodeClass === null) {
            $authCode = Yii::createObject('sweelix\oauth2\server\interfaces\AuthCodeModelInterface');
            $this->authCodeClass = get_class($authCode);
        }
        return $this->authCodeClass;
    }

    /**
     * @return string classname for selected interface
     * @since 1.0.0
     */
    protected function getClientClass()
    {
        if ($this->clientClass === null) {
            $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
            $this->clientClass = get_class($client);
        }
        return $this->clientClass;
    }

    /**
     * @return string classname for selected interface
     * @since 1.0.0
     */
    protected function getCypherKeyClass()
    {
        if ($this->cypherKeyClass === null) {
            $cypherKey = Yii::createObject('sweelix\oauth2\server\interfaces\CypherKeyModelInterface');
            $this->cypherKeyClass = get_class($cypherKey);
        }
        return $this->cypherKeyClass;
    }

    /**
     * @return string classname for selected interface
     * @since 1.0.0
     */
    protected function getJtiClass()
    {
        if ($this->jtiClass === null) {
            $jti = Yii::createObject('sweelix\oauth2\server\interfaces\JtiModelInterface');
            $this->jtiClass = get_class($jti);
        }
        return $this->jtiClass;
    }

    /**
     * @return string classname for selected interface
     * @since 1.0.0
     */
    protected function getJwtClass()
    {
        if ($this->jwtClass === null) {
            $jwt = Yii::createObject('sweelix\oauth2\server\interfaces\JwtModelInterface');
            $this->jwtClass = get_class($jwt);
        }
        return $this->jwtClass;
    }

    /**
     * @return string classname for selected interface
     * @since 1.0.0
     */
    protected function getRefreshTokenClass()
    {
        if ($this->refreshTokenClass === null) {
            $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
            $this->refreshTokenClass = get_class($refreshToken);
        }
        return $this->refreshTokenClass;
    }

    /**
     * @return string classname for selected interface
     * @since 1.0.0
     */
    public function getScopeClass()
    {
        if ($this->scopeClass === null) {
            $scope = Yii::createObject('sweelix\oauth2\server\interfaces\ScopeModelInterface');
            $this->scopeClass = get_class($scope);
        }
        return $this->scopeClass;
    }

    /**
     * @return string classname for selected interface
     * @since 1.0.0
     */
    public function getUserClass()
    {
        if ($this->userClass === null) {
            $scope = Yii::createObject('sweelix\oauth2\server\interfaces\UserModelInterface');
            $this->userClass = get_class($scope);
        }
        return $this->userClass;
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
        return $accessToken->save();
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
            return $accessToken->delete();
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getAuthorizationCode($code)
    {
        $authCodeClass = $this->getAuthCodeClass();
        $authCode = $authCodeClass::findOne($code);
        if ($authCode !== null) {
            $finalCode = [
                'client_id' => $authCode->clientId,
                'user_id' => $authCode->userId,
                'expires' => $authCode->expiry,
                'redirect_uri' => $authCode->redirectUri,
                'scope' => implode(' ', $authCode->scopes),
                'id_token' => $authCode->tokenId,
            ];
            $authCode = $finalCode;
        }
        return $authCode;
    }

    /**
     * @inheritdoc
     */
    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null)
    {
        $authCode = Yii::createObject('sweelix\oauth2\server\interfaces\AuthCodeModelInterface');
        $authCode->id = $code;
        $authCode->clientId = $client_id;
        $authCode->userId = $user_id;
        $authCode->redirectUri = $redirect_uri;
        $authCode->expiry = $expires;
        $authCode->tokenId = $id_token;
        if ($scope === null) {
            $scopes = [];
        } else {
            $scopes = explode(' ', $scope);
        }
        $authCode->scopes = $scopes;
        return $authCode->save();
    }

    /**
     * @inheritdoc
     */
    public function expireAuthorizationCode($code)
    {
        $authCodeClass = $this->getAuthCodeClass();
        $authCode = $authCodeClass::findOne($code);
        if ($authCode !== null) {
            return $authCode->delete();
        }
        return true;
    }
    /**
     * @inheritdoc
     */
    public function getClientDetails($client_id)
    {
        $clientClass = $this->getClientClass();
        $client = $clientClass::findOne($client_id);
        if ($client !== null) {
            $finalClient = [
                'redirect_uri' => is_array($client->redirectUri) ? implode(' ', $client->redirectUri) : $client->redirectUri,
                'client_id' => $client->id,
                'grant_types' => $client->grantTypes,
                'user_id' => $client->userId,
                'scope' => implode(' ', $client->scopes),
            ];
            $client = $finalClient;
        }
        return ($client !== null) ? $client : false;
    }

    /**
     * @inheritdoc
     */
    public function getClientScope($client_id)
    {
        $clientClass = $this->getClientClass();
        $client = $clientClass::findOne($client_id);
        $scopes = '';
        if ($client !== null) {
            $scopes = implode(' ', $client->scopes);
        }
        return $scopes;
    }

    /**
     * @inheritdoc
     */
    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        $clientClass = $this->getClientClass();
        $client = $clientClass::findOne($client_id);
        $notRestricted = true;
        if ($client !== null) {
            if (empty($client->grantTypes) === false) {
                $notRestricted = in_array($grant_type, $client->grantTypes);
            }
        }
        return $notRestricted;
    }

    /**
     * @inheritdoc
     */
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        $clientClass = $this->getClientClass();
        $client = $clientClass::findOne($client_id);
        return ($client !== null) ? ($client->secret === $client_secret) : false;
    }

    /**
     * @inheritdoc
     */
    public function isPublicClient($client_id)
    {
        $clientClass = $this->getClientClass();
        $client = $clientClass::findOne($client_id);
        return ($client !== null) ? $client->isPublic : false;
    }

    /**
     * @inheritdoc
     */
    public function getJti($client_id, $subject, $audience, $expiration, $jti)
    {
        $jtiClass = $this->getJtiClass();
        $jtiModel = $jtiClass::findOne([
            'clientId' => $client_id,
            'subject' => $subject,
            'audience' => $audience,
            'expires' => $expiration,
            'jti' => $jti,
        ]);
        if ($jtiModel !== null) {
            $finalJti = [
                'issuer' => $jtiModel->clientId,
                'subject' => $jtiModel->subject,
                'audience' => $jtiModel->audience,
                'expires' => $jtiModel->expires,
                'jti' => $jtiModel->jti,
            ];
            $jtiModel = $finalJti;
        }
        return $jtiModel;
    }

    /**
     * @inheritdoc
     */
    public function setJti($client_id, $subject, $audience, $expiration, $jti)
    {
        $jtiModel = Yii::createObject('sweelix\oauth2\server\interfaces\JtiModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\JtiModelInterface $jtiModel */
        $jtiModel->clientId = $client_id;
        $jtiModel->subject = $subject;
        $jtiModel->audience = $audience;
        $jtiModel->expires = $expiration;
        $jtiModel->jti = $jti;
        return $jtiModel->save();
    }

    /**
     * @inheritdoc
     */
    public function getClientKey($client_id, $subject)
    {
        $jwtClass = $this->getJwtClass();
        $jwt = $jwtClass::findOne([
            'clientId' => $client_id,
            'subject' => $subject,
        ]);
        if ($jwt !== null) {
            $finalJwt = $jwt->publicKey;
            $jwt = $finalJwt;
        }
        return $jwt;
    }

    /**
     * @inheritdoc
     */
    public function getPublicKey($client_id = null)
    {
        $cypherKeyClass = $this->getCypherKeyClass();
        if ($client_id === null) {
            $client_id = $cypherKeyClass::DEFAULT_KEY;
        }
        $cypherKey = $cypherKeyClass::findOne($client_id);
        if ($cypherKey === null) {
            $cypherKey = $cypherKeyClass::findOne($cypherKeyClass::DEFAULT_KEY);
        }
        if ($cypherKey !== null) {
            $cypherKey = $cypherKey->publicKey;
        }
        return $cypherKey;
    }

    /**
     * @inheritdoc
     */
    public function getPrivateKey($client_id = null)
    {
        $cypherKeyClass = $this->getCypherKeyClass();
        if ($client_id === null) {
            $client_id = $cypherKeyClass::DEFAULT_KEY;
        }
        $cypherKey = $cypherKeyClass::findOne($client_id);
        if ($cypherKey === null) {
            $cypherKey = $cypherKeyClass::findOne($cypherKeyClass::DEFAULT_KEY);
        }
        if ($cypherKey !== null) {
            $cypherKey = $cypherKey->privateKey;
        }
        return $cypherKey;
    }

    /**
     * @inheritdoc
     */
    public function getEncryptionAlgorithm($client_id = null)
    {
        $cypherKeyClass = $this->getCypherKeyClass();
        if ($client_id === null) {
            $client_id = $cypherKeyClass::DEFAULT_KEY;
        }
        $cypherKey = $cypherKeyClass::findOne($client_id);
        if ($cypherKey === null) {
            $cypherKey = $cypherKeyClass::findOne($cypherKeyClass::DEFAULT_KEY);
        }
        if ($cypherKey !== null) {
            $cypherKey = $cypherKey->encryptionAlgorithm;
        }
        return $cypherKey;
    }

    /**
     * @inheritdoc
     */
    public function getRefreshToken($refresh_token)
    {
        $refreshTokenClass = $this->getRefreshTokenClass();
        $refreshToken = $refreshTokenClass::findOne($refresh_token);
        if ($refreshToken !== null) {
            $finalToken = [
                'refresh_token' => $refreshToken->id,
                'client_id' => $refreshToken->clientId,
                'user_id' => $refreshToken->userId,
                'expires' => $refreshToken->expiry,
                'scope' => implode(' ', $refreshToken->scopes),
            ];
            $refreshToken = $finalToken;
        }
        return $refreshToken;
    }

    /**
     * @inheritdoc
     */
    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null)
    {
        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        $refreshToken->id = $refresh_token;
        $refreshToken->clientId = $client_id;
        $refreshToken->userId = $user_id;
        $refreshToken->expiry = $expires;
        if ($scope === null) {
            $scopes = [];
        } else {
            $scopes = explode(' ', $scope);
        }
        $refreshToken->scopes = $scopes;
        return $refreshToken->save();
    }

    /**
     * @inheritdoc
     */
    public function unsetRefreshToken($refresh_token)
    {
        $refreshTokenClass = $this->getRefreshTokenClass();
        $refreshToken = $refreshTokenClass::findOne($refresh_token);
        if ($refreshToken !== null) {
            return $refreshToken->delete();
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function scopeExists($scope)
    {
        $scopeClass = $this->getScopeClass();
        $availableScopes = $scopeClass::findAvailableScopeIds();
        $requestedScopes = explode(' ', $scope);
        $missingScopes = array_diff($requestedScopes, $availableScopes);
        return empty($missingScopes);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultScope($client_id = null)
    {
        $scopeClass = $this->getScopeClass();
        $availableDefaultScopes = $scopeClass::findDefaultScopeIds($client_id);
        $scope = implode(' ', $availableDefaultScopes);
        if (empty($scope) === true) {
            $scope = null;
        }
        return $scope;
    }

    /**
     * @inheritdoc
     */
    public function checkUserCredentials($username, $password)
    {
        $userClass = $this->getUserClass();
        $user = $userClass::findByUsernameAndPassword($username, $password);
        return ($user !== null);
    }

    /**
     * @inheritdoc
     */
    public function getUserDetails($username)
    {
        $userClass = $this->getUserClass();
        $user = $userClass::findByUsername($username);
        /* @var \sweelix\oauth2\server\interfaces\UserModelInterface $user) */
        $details = false;
        if ($user !== null) {
            $details = [
                'user_id' => $user->getId(),
            ];
            $restrictedScopes = $user->getRestrictedScopes();
            if (($restrictedScopes !== null) && (is_array($restrictedScopes) === true)) {
                $details['scope'] = implode(' ', $restrictedScopes);
            }
        }
        return $details;
    }

}
