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
use yii\web\Controller;
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
class AuthorizeController extends Controller
{

    /**
     * @var string
     */
    private $userClass;

    /**
     * @return string classname for selected interface
     * @since XXX
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
     * Send back an oauth token
     * @return Response
     * @since XXX
     */
    public function actionIndex()
    {
        $oauthServer = Yii::createObject('OAuth2\Server');
        /* @var \Oauth2\Server $oauthServer */
        $status = false;
        $oauthRequest = OAuth2Request::createFromGlobals();
        $oauthResponse = new OAuth2Response();
        $grantType = Yii::$app->request->getQueryParam('response_type');
        switch ($grantType) {
            // Authorization Code
            case 'code':
                $oauthGrantType = Yii::createObject('OAuth2\GrantType\AuthorizationCode');
                /* @var \OAuth2\GrantType\AuthorizationCode $oauthGrantType */
                $oauthServer->addGrantType($oauthGrantType);
                $status = $oauthServer->validateAuthorizeRequest($oauthRequest, $oauthResponse);
                $error = $oauthResponse->getParameters();
                if (empty($error) === false) {
                    Yii::$app->session->setFlash('error', $error, false);
                    return $this->redirect(['error']);
                }
                break;
            // Implicit
            case 'token':
                $status = $oauthServer->validateAuthorizeRequest($oauthRequest, $oauthResponse);
                break;
        }

        if ($status === true) {
            Yii::$app->session->set('oauthServer', $oauthServer);
            if (isset($oauthRequest) === true) {
                Yii::$app->session->set('oauthRequest', $oauthRequest);
            }
            $this->redirect(['authorize/login']);
        } else {
            //TODO: check if we should redirect to specific url with an error
            $this->redirect(['authorize/error']);
        }
    }

    /**
     * Display login page
     * @return Response|string
     * @since XXX
     */
    public function actionLogin()
    {
        $oauthServer = Yii::$app->session->get('oauthServer');
        /* @var \Oauth2\Server $oauthServer */
        $oauthController = $oauthServer->getAuthorizeController();
        $userForm = Yii::createObject('sweelix\oauth2\server\forms\User');
        /* @var \sweelix\oauth2\server\forms\User $userForm */
        if (Yii::$app->request->isPost === true) {
            //TODO: handle case when user decline the grants
            $userForm->load(Yii::$app->request->bodyParams);
            if ($userForm->validate() === true) {
                $userClass = $this->getUserClass();
                $realUser = $userClass::findByUsernameAndPassword($userForm->username, $userForm->password);
                /* @var \sweelix\oauth2\server\interfaces\UserModelInterface $realUser */
                if ($realUser !== null) {
                    //login successful
                    $oauthRequest = Yii::$app->session->get('oauthRequest');
                    $oauthResponse = new OAuth2Response();
                    $oauthResponse = $oauthServer->handleAuthorizeRequest($oauthRequest, $oauthResponse, true, $realUser->getId());
                    /* @var OAuth2Response $oauthResponse */
                    Yii::$app->session->remove('oauthServer');
                    Yii::$app->session->remove('oauthRequest');
                    $error = $oauthResponse->getParameters();
                    if (empty($error) === false) {
                        Yii::$app->session->setFlash('error', $error, false);
                        return $this->redirect(['error']);
                    } else {
                        return $this->redirect($oauthResponse->getHttpHeader('Location'));
                    }
                } else {
                    $userForm->addError('username');
                }
            }
        }
        return $this->render('login', [
            'user' => $userForm
        ]);
    }

    /**
     * Display an error page
     * @return Response|string
     * @since XXX
     */
    public function actionError()
    {
        $errorData = Yii::$app->session->getFlash('error');
        return $this->render('error', [
            'type' => (isset($errorData['error']) ? $errorData['error'] : null),
            'description' => (isset($errorData['error_description']) ? $errorData['error_description'] : null),
        ]);
    }

}
