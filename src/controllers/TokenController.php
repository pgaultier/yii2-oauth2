<?php
/**
 * DefaultController.php
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package sweelix\oauth2\server\controllers
 */

namespace sweelix\oauth2\server\controllers;

use OAuth2\Request as OAuth2Request;
use OAuth2\Response as OAuth2Response;
use sweelix\oauth2\server\Module;
use yii\rest\Controller;
use yii\web\Response;
use Yii;

/**
 * Oauth2 main controller
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package sweelix\oauth2\server\controllers
 * @since XXX
 */
class TokenController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);
        unset($behaviors['rateLimiter']);
        return $behaviors;
    }

    /**
     * Send back an oauth token
     * @return Response
     * @since XXX
     */
    public function actionIndex()
    {
        $oauthServer = Yii::createObject('OAuth2\Server');
        /* @var \Oauth2\Server $oauthServer */
        $grantType = Yii::$app->request->getBodyParam('grant_type');
        switch ($grantType) {
            // Client Credentials
            case 'client_credentials':
                if (Module::getInstance()->allowClientCredentials === true) {
                    $oauthGrantType = Yii::createObject('OAuth2\GrantType\ClientCredentials');
                    /* @var \OAuth2\GrantType\ClientCredentials $oauthGrantType */
                    $oauthServer->addGrantType($oauthGrantType);
                }
                break;
            // Resource Owner Password Credentials
            case 'password':
                if (Module::getInstance()->allowPassword === true) {
                    $oauthGrantType = Yii::createObject('OAuth2\GrantType\UserCredentials');
                    /* @var \OAuth2\GrantType\UserCredentials $oauthGrantType */
                    $oauthServer->addGrantType($oauthGrantType);
                }
                break;
            // Refresh Token
            case 'refresh_token':
                if (Module::getInstance()->allowRefreshToken === true) {
                    $oauthGrantType = Yii::createObject('OAuth2\GrantType\RefreshToken');
                    /* @var \OAuth2\GrantType\RefreshToken $oauthGrantType */
                    $oauthServer->addGrantType($oauthGrantType);
                }
                break;
            case 'authorization_code':
                if (Module::getInstance()->allowAuthorizationCode === true) {
                    $oauthGrantType = Yii::createObject('OAuth2\GrantType\AuthorizationCode');
                    /* @var \OAuth2\GrantType\AuthorizationCode $oauthGrantType */
                    $oauthServer->addGrantType($oauthGrantType);
                }
                break;
            case 'urn:ietf:params:oauth:grant-type:jwt-bearer':
                $oauthGrantType = Yii::createObject('OAuth2\GrantType\RefreshToken');
                /* @var \OAuth2\GrantType\JwtBearer $oauthGrantType */
                $oauthServer->addGrantType($oauthGrantType);
                break;
        }

        $response = $oauthServer->handleTokenRequest(OAuth2Request::createFromGlobals());
        $response = $this->convertResponse($response);
        return $response;
    }

    /**
     * convert OAuth2 response to Yii2 response
     * @param OAuth2Response $oauthResponse
     * @return \yii\web\Response
     * @since XXX
     */
    protected function convertResponse(OAuth2Response $oauthResponse)
    {
        $rawContentType = Yii::$app->request->getContentType();
        if (($pos = strpos($rawContentType, ';')) !== false) {
            // e.g. application/json; charset=UTF-8
            $contentType = substr($rawContentType, 0, $pos);
        } else {
            $contentType = $rawContentType;
        }
        $response = Yii::$app->response;
        $response->statusCode = $oauthResponse->getStatusCode();
        $response->statusText = $oauthResponse->getStatusText();
        if ($contentType === 'application/json') {
            $response->content = $oauthResponse->getResponseBody();
        } else {
            $response->content = $oauthResponse->getResponseBody('xml');
        }
        $headers = $oauthResponse->getHttpHeaders();
        foreach($headers as $name => $value)
        {
            $response->headers->set($name, $value);
        }
        return $response;
    }
}
