<?php

namespace sweelix\oauth2\server\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class CronJobController extends Controller
{
    /**
     * Remove expired tokens from database
     * @return int
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRemoveExpired()
    {
        $accessToken = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\AccessTokenModelInterface $accessToken */
        $accessTokenClass = get_class($accessToken);
        $accessTokenClass::deleteAllExpired();

        $jti = Yii::createObject('sweelix\oauth2\server\interfaces\JtiModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\JtiModelInterface $jti */
        $jtiClass = get_class($jti);
        $jtiClass::deleteAllExpired();

        $refreshToken = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\RefreshTokenModelInterface $refreshToken */
        $refreshTokenClass = get_class($refreshToken);
        $refreshTokenClass::deleteAllExpired();

        return ExitCode::OK;
    }
}
