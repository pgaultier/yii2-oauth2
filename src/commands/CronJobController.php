<?php

namespace sweelix\oauth2\server\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class CronJobController extends Controller
{
    /**
     * @return int
     * @throws \yii\db\Exception
     */
    public function actionRemoveExpired()
    {
        $tokenSuppressedNumber = 0;
        $tokenSuppressedNumber += Yii::$app->db->createCommand()
            ->delete('oauthAccessTokens', 'expiry <= NOW()')
            ->execute();
        $tokenSuppressedNumber += Yii::$app->db->createCommand()
            ->delete('oauthAuthorizationCodes', 'expiry <= NOW()')
            ->execute();
        $tokenSuppressedNumber += Yii::$app->db->createCommand()
            ->delete('oauthJtis', 'expires <= NOW()')
            ->execute();
        $tokenSuppressedNumber += Yii::$app->db->createCommand()
            ->delete('oauthRefreshTokens', 'expiry <= NOW()')
            ->execute();
        $this->stdout($tokenSuppressedNumber."\n");
        return ExitCode::OK;
    }
}
