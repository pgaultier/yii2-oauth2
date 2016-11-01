<?php
/**
 * KeyController.php
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package sweelix\oauth2\server\commands
 */

namespace sweelix\oauth2\server\commands;

use yii\console\Controller;
use Yii;

/**
 * Manage oauth keys
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package sweelix\oauth2\server\commands
 * @since XXX
 */
class KeyController extends Controller
{

    public $id;
    public $publicKey;
    public $privateKey;
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
     * @param string $id
     * @return int
     * @since XXX
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
