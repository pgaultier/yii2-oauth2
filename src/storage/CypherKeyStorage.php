<?php
/**
 * CypherKeyStorage.php
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 */

namespace sweelix\oauth2\server\storage;

use OAuth2\Storage\PublicKeyInterface;
use Yii;

class CypherKeyStorage implements PublicKeyInterface
{
    /**
     * @var string
     */
    private $cypherKeyClass;

    /**
     * @return string classname for selected interface
     * @since XXX
     */
    public function getCypherKeyClass()
    {
        if ($this->cypherKeyClass === null) {
            $client = Yii::createObject('sweelix\oauth2\server\interfaces\CypherKeyModelInterface');
            $this->cypherKeyClass = get_class($client);
        }
        return $this->cypherKeyClass;
    }

    /**
     * @inheritdoc
     */
    public function getPublicKey($client_id = null)
    {
        $cypherKeyClass = $this->getCypherKeyClass();
        $cypherKey = $cypherKeyClass::findOne($client_id);
        if ($cypherKey === null) {
            $cypherKey = $cypherKeyClass::findOne($cypherKeyClass::DEFAULT_KEY);
        }
        if ($cypherKey !== null) {
            $cypherKey = $cypherKey->publicKey;
        }
        return $cypherKey;
    }

    /**
     * @inheritdoc
     */
    public function getPrivateKey($client_id = null)
    {
        $cypherKeyClass = $this->getCypherKeyClass();
        $cypherKey = $cypherKeyClass::findOne($client_id);
        if ($cypherKey === null) {
            $cypherKey = $cypherKeyClass::findOne($cypherKeyClass::DEFAULT_KEY);
        }
        if ($cypherKey !== null) {
            $cypherKey = $cypherKey->privateKey;
        }
        return $cypherKey;
    }

    /**
     * @inheritdoc
     */
    public function getEncryptionAlgorithm($client_id = null)
    {
        $cypherKeyClass = $this->getCypherKeyClass();
        $cypherKey = $cypherKeyClass::findOne($client_id);
        if ($cypherKey === null) {
            $cypherKey = $cypherKeyClass::findOne($cypherKeyClass::DEFAULT_KEY);
        }
        if ($cypherKey !== null) {
            $cypherKey = $cypherKey->encryptionAlgoritm;
        }
        return $cypherKey;
    }
}