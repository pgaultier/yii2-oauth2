<?php
/**
 * DefaultController.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\controllers
 */

namespace sweelix\oauth2\server\controllers;

use OAuth2\Request as OAuth2Request;
use OAuth2\Response as OAuth2Response;
use sweelix\oauth2\server\Module;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;
use Yii;

/**
 * Oauth2 main controller
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\controllers
 * @since 1.0.0
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

        if (Module::getInstance()->cors !== false) {
            $corsFilter = ArrayHelper::merge([
                'Access-Control-Request-Method' => ['POST', 'OPTIONS'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 3600,
            ], Module::getInstance()->cors);

            $behaviors['corsFilter'] = [
                'class' => Cors::className(),
                'cors' => $corsFilter,
            ];
        }
        return $behaviors;
    }

    /**
     * Send back an oauth token
     * @return Response|array
     * @since 1.0.0
     */
    public function actionIndex()
    {
        $oauthServer = Yii::createObject('OAuth2\Server');
        /* @var \Oauth2\Server $oauthServer */
        $grantType = Yii::$app->request->getBodyParam('grant_type');
        $grantIsValid = false;
        switch ($grantType) {
            // Client Credentials
            case 'client_credentials':
                if (Module::getInstance()->allowClientCredentials === true) {
                    $grantIsValid = true;
                    $oauthGrantType = Yii::createObject('OAuth2\GrantType\ClientCredentials');
                    /* @var \OAuth2\GrantType\ClientCredentials $oauthGrantType */
                    $oauthServer->addGrantType($oauthGrantType);
                }
                break;
            // Resource Owner Password Credentials
            case 'password':
                if (Module::getInstance()->allowPassword === true) {
                    $grantIsValid = true;
                    $oauthGrantType = Yii::createObject('OAuth2\GrantType\UserCredentials');
                    /* @var \OAuth2\GrantType\UserCredentials $oauthGrantType */
                    $oauthServer->addGrantType($oauthGrantType);
                }
                break;
            // Refresh Token
            case 'refresh_token':
                $grantIsValid = true;
                $oauthGrantType = Yii::createObject('OAuth2\GrantType\RefreshToken');
                /* @var \OAuth2\GrantType\RefreshToken $oauthGrantType */
                $oauthServer->addGrantType($oauthGrantType);
                break;
            case 'authorization_code':
                if (Module::getInstance()->allowAuthorizationCode === true) {
                    $grantIsValid = true;
                    $oauthGrantType = Yii::createObject('OAuth2\GrantType\AuthorizationCode');
                    /* @var \OAuth2\GrantType\AuthorizationCode $oauthGrantType */
                    $oauthServer->addGrantType($oauthGrantType);
                }
                break;
            case 'urn:ietf:params:oauth:grant-type:jwt-bearer':
                $grantIsValid = true;
                $oauthGrantType = Yii::createObject('OAuth2\GrantType\JwtBearer');
                /* @var \OAuth2\GrantType\JwtBearer $oauthGrantType */
                $oauthServer->addGrantType($oauthGrantType);
                break;
        }

        if ($grantIsValid === true) {
            $response = $oauthServer->handleTokenRequest(OAuth2Request::createFromGlobals());
            $response = $this->convertResponse($response);
        } else {
            $response = [
                'error' => 'invalid_grant',
                'error_description' => $grantType.' doesn\'t exist or is invalid for the client.'
            ];
        }
        return $response;
    }

    /**
     * Basic options response for cors
     * @return null|\yii\web\Response
     * @since 1.1.0
     * @throws MethodNotAllowedHttpException
     */
    public function actionOptions()
    {
        if (Module::getInstance()->cors === false) {
            throw new MethodNotAllowedHttpException();
        }
        return null;
    }

    /**
     * convert OAuth2 response to Yii2 response
     * @param OAuth2Response $oauthResponse
     * @return \yii\web\Response
     * @since 1.0.0
     */
    protected function convertResponse(OAuth2Response $oauthResponse)
    {
        //TODO: check if we should use acceptable contentType
        /*
        $acceptableContentTypes = Yii::$app->request->getAcceptableContentTypes();
        foreach ($acceptableContentTypes as $acceptableContentType => $q) {
            $rawContentType = $acceptableContentType;
            if (($pos = strpos($rawContentType, ';')) !== false) {
                // e.g. application/json; charset=UTF-8
                $contentType = substr($rawContentType, 0, $pos);
            } else {
                $contentType = $rawContentType;
            }
            break;
        }
        */
        $contentType = 'application/json';
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
