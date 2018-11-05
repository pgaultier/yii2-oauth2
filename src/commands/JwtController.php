<?php
/**
 * JwtController.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\commands
 */

namespace sweelix\oauth2\server\commands;

use yii\console\Controller;
use Yii;
use yii\console\ExitCode;

/**
 * Manage oauth jwts
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\commands
 * @since 1.0.0
 */
class JwtController extends Controller
{
    public $clientId;
    public $subject;

    /**
     * @var string alias to public key file
     */
    public $publicKey;

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return [
            'clientId',
            'subject',
            'publicKey',
        ];
    }

    /**
     * Create new Oauth Jwt
     * @param string $clientId Should be clientId
     * @param string $subject
     * @param string $publicKey
     * @return int
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\UnknownClassException
     * @since 1.0.0
     */
    public function actionCreate($clientId, $subject, $publicKey)
    {
        $jwt = Yii::createObject('sweelix\oauth2\server\interfaces\JwtModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\JwtModelInterface $jwt */
        $jwt->clientId = $clientId;
        $jwt->subject = $subject;
        if ($this->publicKey !== null) {
            $this->publicKey = Yii::getAlias($this->publicKey);
            if (file_exists($this->publicKey) === true) {
                $jwt->publicKey = file_get_contents($this->publicKey);
            }
        } else {
            $jwt->publicKey = $publicKey;
        }
        if ($jwt->save() === true) {
            $this->stdout('Jwt created :' . "\n");
            $this->stdout(' - id: ' . $jwt->id . "\n");
            $this->stdout(' - clientId: ' . $jwt->clientId . "\n");
            $this->stdout(' - subject: ' . $jwt->subject . "\n");
            $this->stdout(' - publicKey: ' . $jwt->publicKey . "\n");
            return ExitCode::OK;
        } else {
            $this->stdout('Jwt cannot be created.' . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Update Oauth jwt
     * @param string $id Jwt id
     * @return int
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\UnknownClassException
     */
    public function actionUpdate($id)
    {
        $jwt = Yii::createObject('sweelix\oauth2\server\interfaces\JwtModelInterface');
        $jwtClass = get_class($jwt);
        /* @var \sweelix\oauth2\server\interfaces\JwtModelInterface $jwt */
        $jwt = $jwtClass::findOne($id);
        if ($jwt !== null) {
            $jwt->clientId = $this->clientId;
            $jwt->subject = $this->subject;
            $jwt->publicKey = $this->publicKey;
            if ($jwt->save() === true) {
                $this->stdout('Jwt updated :' . "\n");
                $this->stdout(' - id: ' . $jwt->id . "\n");
                $this->stdout(' - clientId: ' . $jwt->clientId . "\n");
                $this->stdout(' - subject: ' . $jwt->subject . "\n");
                $this->stdout(' - publicKey: ' . $jwt->publicKey . "\n");
                return ExitCode::OK;
            } else {
                $this->stdout('Jwt ' . $id . ' cannot be updated' . "\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }
        } else {
            $this->stdout('Jwt ' . $id . ' does not exist' . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Delete Oauth jwt
     * @param string $id Jwt id
     * @return int
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\UnknownClassException
     */
    public function actionDelete($id)
    {
        $jwt = Yii::createObject('sweelix\oauth2\server\interfaces\JwtModelInterface');
        $jwtClass = get_class($jwt);
        /* @var \sweelix\oauth2\server\interfaces\JwtModelInterface $jwt */
        $jwt = $jwtClass::findOne($id);
        if ($jwt !== null) {
            if ($jwt->delete() === true) {
                $this->stdout('Jwt ' . $id . ' deleted' . "\n");
                return ExitCode::OK;
            } else {
                $this->stdout('Jwt ' . $id . ' cannot be deleted' . "\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }
        } else {
            $this->stdout('Jwt ' . $id . ' does not exist' . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}
