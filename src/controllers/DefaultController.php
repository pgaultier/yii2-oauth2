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
use yii\rest\Controller;
use Yii;
use yii\web\Response;

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
class DefaultController extends Controller
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

    public function actionToken()
    {
        $oauthServer = Yii::createObject('OAuth2\Server');
        /* @var \Oauth2\Server $oauthServer */
        if (Yii::$app->request->isPost) {
            $grantType = Yii::$app->request->getBodyParam('grant_type');
            switch ($grantType) {
                case 'client_credentials':
                    $oauthGrantType = Yii::createObject('OAuth2\GrantType\ClientCredentials');
                    /* @var \OAuth2\GrantType\ClientCredentials $oauthGrantType */
                    $oauthServer->addGrantType($oauthGrantType);
                    break;
                case 'password':
                    $oauthGrantType = Yii::createObject('OAuth2\GrantType\UserCredentials');
                    /* @var \OAuth2\GrantType\UserCredentials $oauthGrantType */
                    $oauthServer->addGrantType($oauthGrantType);
                    break;
            }
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