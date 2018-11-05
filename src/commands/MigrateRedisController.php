<?php

namespace sweelix\oauth2\server\commands;

use sweelix\oauth2\server\interfaces\AccessTokenModelInterface;
use sweelix\oauth2\server\interfaces\JtiModelInterface;
use sweelix\oauth2\server\interfaces\RefreshTokenModelInterface;
use sweelix\oauth2\server\services\redis\AccessTokenService;
use sweelix\oauth2\server\services\redis\ClientService;
use sweelix\oauth2\server\services\redis\JtiService;
use sweelix\oauth2\server\services\redis\RefreshTokenService;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use sweelix\oauth2\server\Module;
use yii\di\Instance;
use yii\redis\Connection;

class MigrateRedisController extends Controller
{
    public function actionMigrate()
    {
        try {
            $module = Module::getInstance();
            /** @var Connection $db */
            $db = Instance::ensure($module->db, Connection::class);

            ///////////////
            ///
            /// AccessToken
            ///
            ///////////////
            $this->stdout('Start migrating AccessTokens ...' . "\n");
            /** @var AccessTokenService $accessTokenService */
            $accessTokenService = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenServiceInterface');
            $accessTokenList = $accessTokenService->getAccessTokenListKey();
            if ($db->executeCommand('EXISTS', [$accessTokenList]) == 1) {
                $prompt = $this->prompt('The key "'.$accessTokenList.'" already exists. Do you want to continue ? (yes, no)', [
                    'required' => true,
                ]);
                switch ($prompt) {
                    case 'no':
                    case 'n':
                        exit();
                }
            }
            $userListKey = $accessTokenService->getUserListKey();
            if ($db->executeCommand('EXISTS', [$userListKey]) == 1) {
                $prompt = $this->prompt('The key "'.$userListKey.'" already exists. Do you want to continue ? (yes, no)', [
                    'required' => true,
                ]);
                switch ($prompt) {
                    case 'no':
                    case 'n':
                        exit();
                }
            }
            $accessToken = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
            $accessTokenClass = get_class($accessToken);
            $accessTokens = $db->executeCommand('KEYS', [$accessTokenService->getUserAccessTokensKey('*')]);
            if (!empty($accessTokens)) {
                $db->executeCommand('DEL', $accessTokens);
            }
            $accessTokens = $db->executeCommand('KEYS', [$accessTokenService->getClientAccessTokensKey('*')]);
            if (!empty($accessTokens)) {
                $db->executeCommand('DEL', $accessTokens);
            }
            $accessTokens = $db->executeCommand('KEYS', [$accessTokenService->getAccessTokenKey('*')]);
            foreach ($accessTokens as $accessTokenHash) {
                if ($db->executeCommand('TYPE', [$accessTokenHash]) !== 'hash') {
                    continue;
                }
                $accessTokenData = $db->executeCommand('HGETALL', [$accessTokenHash]);
                if (!empty($accessTokenData)) {
                    /** @var AccessTokenModelInterface $accessToken */
                    $accessToken = $accessTokenClass::findOne($accessTokenData[1]); // id
                    if (!empty($accessToken)) {
                        $accessToken->delete();
                        $accessToken->save();
                    }
                }
            }
            $this->stdout('Done migrating AccessTokens !' . "\n");

            ///////////////
            ///
            /// Clients
            ///
            ///////////////
            $this->stdout('Start migrating Clients ...' . "\n");
            /** @var ClientService $clientService */
            $clientService = Yii::createObject('sweelix\oauth2\server\interfaces\ClientServiceInterface');
            $clientList = $clientService->getClientListKey();
            if ($db->executeCommand('EXISTS', [$clientList]) == 1) {
                $prompt = $this->prompt('The key "'.$clientList.'" already exists. Do you want to continue ? (yes, no)', [
                    'required' => true,
                ]);
                switch ($prompt) {
                    case 'no':
                    case 'n':
                        exit();
                }
            }
            $clients = $db->executeCommand('KEYS', [$clientService->getClientKey('*')]);
            foreach ($clients as $clientHash) {
                if ($db->executeCommand('TYPE', [$clientHash]) !== 'hash') {
                    continue;
                }
                $clientData = $db->executeCommand('HGETALL', [$clientHash]);
                if (!empty($clientData)) {
                    $db->executeCommand('SADD', [$clientService->getClientListKey(), $clientData[1]]);
                }
            }
            $this->stdout('Done migrating Clients !' . "\n");

            ///////////////
            ///
            /// Jti
            ///
            ///////////////
            $this->stdout('Start migrating Jtis ...' . "\n");
            /** @var JtiService $jtiService */
            $jtiService = Yii::createObject('sweelix\oauth2\server\interfaces\JtiServiceInterface');
            $jtiList = $jtiService->getJtiListKey();
            if ($db->executeCommand('EXISTS', [$jtiList]) == 1) {
                $prompt = $this->prompt('The key "'.$jtiList.'" already exists. Do you want to continue ? (yes, no)', [
                    'required' => true,
                ]);
                switch ($prompt) {
                    case 'no':
                    case 'n':
                        exit();
                }
            }
            $jti = Yii::createObject('sweelix\oauth2\server\interfaces\JtiModelInterface');
            $jtiClass = get_class($jti);
            $jtis = $db->executeCommand('KEYS', [$jtiService->getSubjectJtisKey('*')]);
            if (!empty($jtis)) {
                $db->executeCommand('DEL', $jtis);
            }
            $jtis = $db->executeCommand('KEYS', [$jtiService->getSubjectJtisKey('*')]);
            if (!empty($jtis)) {
                $db->executeCommand('DEL', $jtis);
            }
            $jtis = $db->executeCommand('KEYS', [$jtiService->getJtiKey('*')]);
            foreach ($jtis as $jtiHash) {
                if ($db->executeCommand('TYPE', [$jtiHash]) !== 'hash') {
                    continue;
                }
                $jtiData = $db->executeCommand('HGETALL', [$jtiHash]);
                if (!empty($jtiData)) {
                    /** @var JtiModelInterface $jti */
                    $jti = $jtiClass::findOne($jtiData[1]); // id
                    if (!empty($jti)) {
                        $jti->delete();
                        $jti->save();
                    }
                }
            }
            $this->stdout('Done migrating Jtis !' . "\n");

            ///////////////
            ///
            /// RefreshToken
            ///
            ///////////////
            $this->stdout('Start migrating RefreshTokens ...' . "\n");
            /** @var RefreshTokenService $refreshTokenService */
            $refreshTokenService = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenServiceInterface');
            $refreshTokenList = $refreshTokenService->getRefreshTokenListKey();
            if ($db->executeCommand('EXISTS', [$refreshTokenList]) == 1) {
                $prompt = $this->prompt('The key "'.$refreshTokenList.'" already exists. Do you want to continue ? (yes, no)', [
                    'required' => true,
                ]);
                switch ($prompt) {
                    case 'no':
                    case 'n':
                        exit();
                }
            }
            $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
            $refreshTokenClass = get_class($refreshToken);
            $refreshTokens = $db->executeCommand('KEYS', [$refreshTokenService->getUserRefreshTokensKey('*')]);
            if (!empty($refreshTokens)) {
                $db->executeCommand('DEL', $refreshTokens);
            }
            $refreshTokens = $db->executeCommand('KEYS', [$refreshTokenService->getClientRefreshTokensKey('*')]);
            if (!empty($refreshTokens)) {
                $db->executeCommand('DEL', $refreshTokens);
            }
            $refreshTokens = $db->executeCommand('KEYS', [$refreshTokenService->getRefreshTokenKey('*')]);
            foreach ($refreshTokens as $refreshTokenHash) {
                if ($db->executeCommand('TYPE', [$refreshTokenHash]) !== 'hash') {
                    continue;
                }
                $refreshTokenData = $db->executeCommand('HGETALL', [$refreshTokenHash]);
                if (!empty($refreshTokenData)) {
                    /** @var RefreshTokenModelInterface $refreshToken */
                    $refreshToken = $refreshTokenClass::findOne($refreshTokenData[1]); // id
                    if (!empty($refreshToken)) {
                        $refreshToken->delete();
                        $refreshToken->save();
                    }
                }
            }
            $this->stdout('Done migrating RefreshTokens !' . "\n");

            $this->stdout('Migration done !' . "\n");
            $exitCode = ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr($e->getMessage() . "\n");
            $exitCode = ExitCode::getReason($e->getCode());
        }
        return $exitCode;
    }
}
