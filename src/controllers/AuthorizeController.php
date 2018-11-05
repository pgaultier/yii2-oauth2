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
     * @var string
     */
    private $userClass;

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
            'class' => AccessControl::class,
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
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     * @since 1.0.0
     */
    public function actionIndex()
    {
        Yii::$app->response->headers->add('Content-Security-Policy', 'frame-ancestors \'none\';');

        $status = false;

        /** @var \OAuth2\Server $oauthServer */
        $oauthServer = Yii::createObject('OAuth2\Server');

        /** @var \OAuth2\Request $oauthRequest */
        $oauthRequest = OAuth2Request::createFromGlobals();

        $oauthResponse = new OAuth2Response();

        /** @var string $grantType */
        $grantType = Yii::$app->request->getQueryParam('response_type');
        $promptValues = key_exists('prompt', $oauthRequest->query) ? explode(" ", $oauthRequest->query['prompt']) : [];

        switch ($grantType) {
            case 'code':
                // Authorization Code
                if (Module::getInstance()->allowAuthorizationCode === true) {
                    /** @var \Oauth2\GrantType\AuthorizationCode $oauthGrantType */
                    $oauthGrantType = Yii::createObject('OAuth2\GrantType\AuthorizationCode');
                    $oauthServer->addGrantType($oauthGrantType);
                    $status = $oauthServer->validateAuthorizeRequest($oauthRequest, $oauthResponse);
                } else {
                    $status = false;
                    $oauthResponse->setError(400, 'invalid_grant', 'Authorization code grant is not supported');
                }
                break;
            case 'token':
                // Implicit
                $status = $oauthServer->validateAuthorizeRequest($oauthRequest, $oauthResponse);
                break;
        }

        if ($status === false) {
            // handle request validation error
            $this->handleErrorResponse($oauthResponse);
        } else {
            Yii::$app->session->set('oauthServer', $oauthServer);
            if (isset($oauthRequest) === true) {
                Yii::$app->session->set('oauthRequest', $oauthRequest);
            }

            if (Yii::$app->user->isGuest === true || in_array('login', $promptValues, true)) {
                /** @var \Oauth2\Controller\AuthorizeController $authController */
                $authController = $oauthServer->getAuthorizeController();

                //TODO: check if the user should get logged out
                if (in_array('none', $promptValues, true)) {
                    $response = new OAuth2Response();
                    $response->setRedirect(302, $authController->getRedirectUri(), $authController->getState(),
                        'login_required',
                        'Authentication Request cannot be completed without user authentication.',
                        NULL
                    );
                    $this->handleErrorResponse($response);
                } else {
                    return $this->redirect(['login']);
                }
            } else {
                return $this->redirect(['authorize']);
            }
        }
    }

    /**
     * Display login page
     *
     * @return Response | string
     * @throws \yii\base\InvalidConfigException
     * @since 1.0.0
     */
    public function actionLogin()
    {
        Yii::$app->response->headers->add('Content-Security-Policy', 'frame-ancestors \'none\';');

        /* @var \Oauth2\Server $oauthServer */
        $oauthServer = Yii::$app->session->get('oauthServer');

        if ($oauthServer === null) {
            $response = new OAuth2Response();
            $response->setError(400, 'invalid_request', 'The request was not performed as expected.');
            $this->handleErrorResponse($response);
        }

        $response = null;
        $oauthRequest = Yii::$app->session->get('oauthRequest');
        $promptValues = $oauthRequest && key_exists('prompt', $oauthRequest->query) ? explode(" ", $oauthRequest->query['prompt']) : [];
        /* @var \sweelix\oauth2\server\forms\User $userForm */
        $userForm = Yii::createObject('sweelix\oauth2\server\forms\User');

        if (Yii::$app->request->isPost === true) {
            //TODO: handle case when user decline the grants
            $userForm->load(Yii::$app->request->bodyParams);
            if ($userForm->validate() === true) {
                $userClass = $this->getUserClass();
                /* @var \sweelix\oauth2\server\interfaces\UserModelInterface $realUser */
                $realUser = $userClass::findByUsernameAndPassword($userForm->username, $userForm->password);
                if ($realUser !== null) {
                    Yii::$app->user->login($realUser, Module::getInstance()->loginDuration);
                    $response = $this->redirect(['authorize']);
                } else {
                    $userForm->addError('username');
                }
            }
        }
        if ($response === null) {
            if(in_array('none', $promptValues, true)) {
                /** @var \Oauth2\Controller\AuthorizeController $authController */
                $authController = $oauthServer->getAuthorizeController();
                $oauthResponse = $oauthServer->getResponse();
                $oauthResponse->setRedirect(302, $authController->getRedirectUri(), $authController->getState(),
                    'login_required',
                    'Authentication Request cannot be completed without user authentication.',
                    null
                );
                $this->handleErrorResponse($oauthResponse);
            }
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
     *
     * @return Response | string
     * @throws \yii\base\UnknownClassException
     * @since 1.0.0
     */
    public function actionAuthorize()
    {
        Yii::$app->response->headers->add('Content-Security-Policy', 'frame-ancestors \'none\';');

        /* @var \Oauth2\Server $oauthServer */
        $oauthServer = Yii::$app->session->get('oauthServer');

        if ($oauthServer === NULL) {
            $response = new OAuth2Response();
            $response->setError(400, 'invalid_request', 'The request was not performed as expected.');
            $this->handleErrorResponse($response);
        }

        /** @var \OAuth2\Controller\AuthorizeController $authController */
        $authController = $oauthServer->getAuthorizeController();
        $client = Client::findOne($authController->getClientId());
        $oauthRequest = Yii::$app->session->get('oauthRequest');
        $promptValues = $oauthRequest && key_exists('prompt', $oauthRequest->query) ? explode(" ", $oauthRequest->query['prompt']) : [];

        // user has already granted permission
        if ($client->hasUser(Yii::$app->user->id) === true && !in_array('consent', $promptValues, true)) {
            //TODO: check if all consents should be removed
            /** @var \OAuth2\Response $oauthResponse */
            $oauthResponse = new OAuth2Response();
            $oauthResponse = $oauthServer->handleAuthorizeRequest($oauthRequest, $oauthResponse, true, Yii::$app->user->id);

            if ($oauthResponse->getParameter('error') === NULL) {
                Yii::$app->session->remove('oauthServer');
                Yii::$app->session->remove('oauthRequest');

                return $this->redirect($oauthResponse->getHttpHeader('Location'));
            } else {
                $this->handleErrorResponse($oauthResponse);
            }
        } else {
            // perform regular authorization
            if (in_array('none', $promptValues, true)) {
                $oauthResponse = $oauthServer->getResponse();
                $oauthResponse->setRedirect(302, $authController->getRedirectUri(), $authController->getState(),
                    'consent_required',
                    'Authentication Request cannot be completed without End-User consent.',
                    NULL
                );
                $this->handleErrorResponse($oauthResponse);
            }

            $additionalScopes = $authController->getScope();
            $requestedScopes = [];
            if (empty($additionalScopes) === false) {
                $additionalScopes = explode(' ', $additionalScopes);
                foreach ($additionalScopes as $scope) {
                    $dbScope = Scope::findOne($scope);
                    if ($dbScope !== NULL) {
                        $requestedScopes[] = $dbScope;
                    } else {
                        $response = new OAuth2Response();
                        $response->setError(400, 'invalid_scope', 'Scope ' . $scope . ' does not exist.');
                        $this->handleErrorResponse($response);
                    }
                }
            }
            if (Yii::$app->request->isPost === true) {
                $accept = Yii::$app->request->getBodyParam('accept');
                /** @var \Oauth2\Request $oauthRequest */
                $oauthRequest = Yii::$app->session->get('oauthRequest');
                /** @var \Oauth2\Response $oauthResponse */
                $oauthResponse = new OAuth2Response();

                if ($accept !== NULL) {
                    // authorize
                    $oauthResponse = $oauthServer->handleAuthorizeRequest($oauthRequest, $oauthResponse, true, Yii::$app->user->id);
                    // check if user has not already granted the client
                    if ($client->hasUser(Yii::$app->user->id) === false) {
                        $client->addUser(Yii::$app->user->id);
                    }
                } else {
                    // decline
                    $oauthResponse = $oauthServer->handleAuthorizeRequest($oauthRequest, $oauthResponse, false, Yii::$app->user->id);
                    $client->removeUser(Yii::$app->user->id);
                }

                Yii::$app->session->remove('oauthServer');
                Yii::$app->session->remove('oauthRequest');
                $error = $oauthResponse->getParameters();
                $redirect = $oauthResponse->getHttpHeader('Location');
                if ((empty($error) === false) && ($redirect === NULL)) {
                    Yii::$app->session->setFlash('error', $error, false);
                    return $this->redirect(['error']);
                } else {
                    return $this->redirect($redirect);
                }
            }
        }
        return $this->render('authorize', [
            'client'          => $client,
            'requestedScopes' => $requestedScopes,
        ]);
    }

    /**
     * Display an error page
     *
     * @return Response | string
     * @since 1.0.0
     */
    public function actionError()
    {
        Yii::$app->response->headers->add('Content-Security-Policy', 'frame-ancestors \'none\';');

        $errorData = Yii::$app->session->getFlash('error');
        $code = isset($errorData['code']) && is_numeric($errorData['code']) ? $errorData['code'] : 400;
        Yii::$app->response->setStatusCode($code);

        return $this->render('error', [
            'type' => (isset($errorData['error']) ? $errorData['error'] : null),
            'description' => (isset($errorData['error_description']) ? $errorData['error_description'] : null),
        ]);
    }

    /**
     * Handles OAuth errors by either displaying an error page or redirecting to the callback
     *
     * @param \Oauth2\Response $response
     * @return Response | null
     */
    public function handleErrorResponse($response)
    {
        if ($response->getParameter('error') !== null) {
            Yii::$app->session->remove('oauthServer');
            Yii::$app->session->remove('oauthRequest');

            if ($response->isRedirection()) {
                return $this->redirect($response->getHttpHeader('Location'));
            } else {
                $code = $response->getStatusCode();
                $error = $response->getParameter('error', 'Unkown error');
                $description = $response->getParameter('error_description', 'Please check your request.');
                Yii::$app->session->setFlash('error', [
                    'code' => $code,
                    'error' => $error,
                    'error_description' => $description
                    ], false
                );
                return $this->redirect(['error']);
            }
        }

        return null;
    }

    /**
     * @return string classname for selected interface
     * @throws \yii\base\InvalidConfigException
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
