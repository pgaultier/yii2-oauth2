<?php
/**
 * DefaultController.php
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\controllers
 */

namespace sweelix\oauth2\server\controllers;

use OAuth2\Request as OAuth2Request;
use OAuth2\Response as OAuth2Response;
use sweelix\oauth2\server\models\Client;
use sweelix\oauth2\server\models\Scope;
use sweelix\oauth2\server\Module;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use Yii;

/**
 * Oauth2 main controller
 *
 * @author pgaultier
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\controllers
 * @since 1.0.0
 */
class AuthorizeController extends Controller
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $module = Module::getInstance();

        if ($module->overrideLayout !== null) {
            $this->layout = $module->overrideLayout;
        }

        if ($module->overrideViewPath !== null) {
            $this->setViewPath($module->overrideViewPath);
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['authorize'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['authorize'],
                    'roles' => ['@'],
                ],
            ],
        ];
        return $behaviors;
    }

    /**
     * Send back an oauth token
     * @return Response
     * @since 1.0.0
     */
    public function actionIndex()
    {
        Yii::$app->response->headers->add('Content-Security-Policy', 'frame-ancestors \'none\';');
        $oauthServer = Yii::createObject('OAuth2\Server');
        /* @var \Oauth2\Server $oauthServer */
        $status = false;
        $oauthRequest = OAuth2Request::createFromGlobals();
        $oauthResponse = new OAuth2Response();
        $grantType = Yii::$app->request->getQueryParam('response_type');
        switch ($grantType) {
            // Authorization Code
            case 'code':
                if (Module::getInstance()->allowAuthorizationCode === true) {
                    $oauthGrantType = Yii::createObject('OAuth2\GrantType\AuthorizationCode');
                    /* @var \OAuth2\GrantType\AuthorizationCode $oauthGrantType */
                    $oauthServer->addGrantType($oauthGrantType);
                    $status = $oauthServer->validateAuthorizeRequest($oauthRequest, $oauthResponse);
                    $error = $oauthResponse->getParameters();
                    if (($status === false) && (empty($error) === false)) {
                        Yii::$app->session->setFlash('error', $error, false);
                        // return $this->redirect(['error']);
                    }
                } else {
                    $status = false;
                    Yii::$app->session->setFlash('error', ['error' => 'invalid_grant', 'error_description' => 'authorization code grant is not supported'], false);
                }
                break;
            // Implicit
            case 'token':
                $status = $oauthServer->validateAuthorizeRequest($oauthRequest, $oauthResponse);
                $error = $oauthResponse->getParameters();
                if (($status === false) && (empty($error) === false)) {
                    Yii::$app->session->setFlash('error', $error, false);
                    // return $this->redirect(['error']);
                }
                break;
        }

        if ($status === true) {
            Yii::$app->session->set('oauthServer', $oauthServer);
            if (isset($oauthRequest) === true) {
                Yii::$app->session->set('oauthRequest', $oauthRequest);
            }
            if (Yii::$app->user->isGuest === true) {
                $response = $this->redirect(['login']);
            } else {
                $response = $this->redirect(['authorize']);
            }
        } else {
            //TODO: check if we should redirect to specific url with an error
            $response = $this->redirect(['error']);
        }
        return $response;
    }

    /**
     * Display login page
     * @return Response|string
     * @since 1.0.0
     */
    public function actionLogin()
    {
        Yii::$app->response->headers->add('Content-Security-Policy', 'frame-ancestors \'none\';');

        $oauthServer = Yii::$app->session->get('oauthServer');
        /* @var \Oauth2\Server $oauthServer */
        if ($oauthServer === null) {
            Yii::$app->session->setFlash('error', [
                'error' => 'request_invalid',
                'error_description' => 'The request was not performed as expected.',
            ], false);
            return $this->redirect(['error']);
        }

        $userForm = Yii::createObject('sweelix\oauth2\server\forms\User');
        $response = null;
        /* @var \sweelix\oauth2\server\forms\User $userForm */
        if (Yii::$app->request->isPost === true) {
            //TODO: handle case when user decline the grants
            $userForm->load(Yii::$app->request->bodyParams);
            if ($userForm->validate() === true) {
                $userClass = $this->getUserClass();
                $realUser = $userClass::findByUsernameAndPassword($userForm->username, $userForm->password);
                /* @var \sweelix\oauth2\server\interfaces\UserModelInterface $realUser */
                if ($realUser !== null) {
                    Yii::$app->user->login($realUser, Module::getInstance()->loginDuration);
                    $response = $this->redirect(['authorize']);
                } else {
                    $userForm->addError('username');
                }
            }
        }
        if ($response === null) {
            // force empty password
            $userForm->password = '';
            $response = $this->render('login', [
                'user' => $userForm,
            ]);
        }
        return $response;
    }

    /**
     * Display authorize page
     * @return string|Response
     * @since 1.0.0
     */
    public function actionAuthorize()
    {
        Yii::$app->response->headers->add('Content-Security-Policy', 'frame-ancestors \'none\';');
        $oauthServer = Yii::$app->session->get('oauthServer');
        /* @var \Oauth2\Server $oauthServer */
        if ($oauthServer === null) {
            Yii::$app->session->setFlash('error', [
                'error' => 'request_invalid',
                'error_description' => 'The request was not performed as expected.',
            ], false);
            return $this->redirect(['error']);
        }
        $oauthController = $oauthServer->getAuthorizeController();
        $client = Client::findOne($oauthController->getClientId());

        if ($client->hasUser(Yii::$app->user->id) === true) {
            // already logged
            $oauthRequest = Yii::$app->session->get('oauthRequest');
            $oauthResponse = new OAuth2Response();
            $oauthResponse = $oauthServer->handleAuthorizeRequest($oauthRequest, $oauthResponse, true, Yii::$app->user->id);
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
            // perform regular authorization
            $additionalScopes = $oauthController->getScope();
            $requestedScopes = [];
            if (empty($additionalScopes) === false) {
                $additionalScopes = explode(' ', $additionalScopes);
                foreach($additionalScopes as $scope) {
                    $dbScope = Scope::findOne($scope);
                    if ($dbScope !== null) {
                        $requestedScopes[] = $dbScope;
                    } else {
                        Yii::$app->session->setFlash('error', [
                            'error' => 'invalid_scope',
                            'error_description' => 'Scope '.$scope.' does not exist.',
                        ], false);
                        return $this->redirect(['error']);
                    }
                }
            }
            if (Yii::$app->request->isPost === true) {
                $accept = Yii::$app->request->getBodyParam('accept', null);
                $oauthRequest = Yii::$app->session->get('oauthRequest');
                $oauthResponse = new OAuth2Response();
                /* @var OAuth2Response $oauthResponse */

                if ($accept !== null) {
                    $oauthResponse = $oauthServer->handleAuthorizeRequest($oauthRequest, $oauthResponse, true, Yii::$app->user->id);
                    $client->addUser(Yii::$app->user->id);
                    // authorize
                } else {
                    $oauthResponse = $oauthServer->handleAuthorizeRequest($oauthRequest, $oauthResponse, false, Yii::$app->user->id);
                    $client->removeUser(Yii::$app->user->id);
                    // decline
                }

                Yii::$app->session->remove('oauthServer');
                Yii::$app->session->remove('oauthRequest');
                $error = $oauthResponse->getParameters();
                $redirect = $oauthResponse->getHttpHeader('Location');
                if ((empty($error) === false) && ($redirect === null)) {
                    Yii::$app->session->setFlash('error', $error, false);
                    return $this->redirect(['error']);
                } else {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('authorize', [
            'client' => $client,
            'requestedScopes' => $requestedScopes,
        ]);
    }

    /**
     * Display an error page
     * @return Response|string
     * @since 1.0.0
     */
    public function actionError()
    {
        Yii::$app->response->headers->add('Content-Security-Policy', 'frame-ancestors \'none\';');
        $errorData = Yii::$app->session->getFlash('error');
        return $this->render('error', [
            'type' => (isset($errorData['error']) ? $errorData['error'] : null),
            'description' => (isset($errorData['error_description']) ? $errorData['error_description'] : null),
        ]);
    }

    /**
     * @var string
     */
    private $userClass;

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



}
