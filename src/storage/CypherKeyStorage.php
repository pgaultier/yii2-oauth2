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
use sweelix\oauth2\server\models\CypherKey;

class CypherKeyStorage implements PublicKeyInterface
{
    /**
     * @inheritdoc
     */
    public function getPublicKey($client_id = null)
    {
        $cypherKey = CypherKey::findOne($client_id);
        if ($cypherKey === null) {
            $cypherKey = CypherKey::findOne(CypherKey::DEFAULT_KEY);
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
        $cypherKey = CypherKey::findOne($client_id);
        if ($cypherKey === null) {
            $cypherKey = CypherKey::findOne(CypherKey::DEFAULT_KEY);
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
        $cypherKey = CypherKey::findOne($client_id);
        if ($cypherKey === null) {
            $cypherKey = CypherKey::findOne(CypherKey::DEFAULT_KEY);
        }
        if ($cypherKey !== null) {
            $cypherKey = $cypherKey->encryptionAlgoritm;
        }
        return $cypherKey;
    }
}