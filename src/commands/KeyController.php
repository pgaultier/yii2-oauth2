<?php
/**
 * KeyController.php
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

/**
 * Manage oauth keys
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\commands
 * @since 1.0.0
 */
class KeyController extends Controller
{

    /**
     * @var string alias to public key file
     */
    public $publicKey;
    /**
     * @var string alias to private key file
     */
    public $privateKey;
    /**
     * @var string encryptiion algorithm
     */
    public $encryptionAlgorithm;

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return [
            'publicKey',
            'privateKey',
            'encryptionAlgorithm',
        ];
    }
    /**
     * Create new Oauth CypherKey
     * @param string $id Should be client-id or default for common key
     * @return int
     * @since 1.0.0
     */
    public function actionCreate($id)
    {

        $cypherKey = Yii::createObject('sweelix\oauth2\server\interfaces\CypherKeyModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\CypherKeyModelInterface $cypherKey */
        $cypherKey->id = $id;
        $hasKey = false;
        if (($this->privateKey !== null) && ($this->publicKey !== null)) {
            $this->privateKey = Yii::getAlias($this->privateKey);
            $this->publicKey = Yii::getAlias($this->publicKey);
            if ((file_exists($this->privateKey) === true) && (file_exists($this->publicKey) === true)) {
                $cypherKey->privateKey = file_get_contents($this->privateKey);
                $cypherKey->publicKey = file_get_contents($this->publicKey);
                $hasKey = true;
            }
        }
        if ($hasKey === false) {
            $cypherKey->generateKeys();
        }
        if ($cypherKey->save() === true) {
            $this->stdout('Key created :'."\n");
            $this->stdout(' - id: ' . $cypherKey->id . "\n");
            $this->stdout(' - Algorithm: ' . $cypherKey->encryptionAlgorithm . "\n");
        } else {
            $this->stdout('Key cannot be created.'."\n");
        }
    }

}
