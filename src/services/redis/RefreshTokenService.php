<?php
/**
 * RefreshTokenService.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\redis
 */

namespace sweelix\oauth2\server\services\redis;

use sweelix\oauth2\server\exceptions\DuplicateIndexException;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
use sweelix\oauth2\server\interfaces\RefreshTokenModelInterface;
use sweelix\oauth2\server\interfaces\RefreshTokenServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;

/**
 * This is the refresh token service for redis
 *  database structure
 *    * oauth2:refreshTokens:<rid> : hash (RefreshToken)
 *    * oauth2:users:<uid>:refreshTokens : set (RefreshTokens for user)
 *    * oauth2:clients:<cid>:refreshTokens : set (RefreshTokens for client)
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\redis
 * @since 1.0.0
 */
class RefreshTokenService extends BaseService implements RefreshTokenServiceInterface
{
    /**
     * @var string user namespace (collection for refreshtokens)
     */
    public $userNamespace = '';

    /**
     * @var string client namespace (collection for refreshtokens)
     */
    public $clientNamespace = '';

    /**
     * @param string $rid refresh token ID
     * @return string refresh token Key
     * @since 1.0.0
     */
    public function getRefreshTokenKey($rid)
    {
        return $this->namespace . ':' . $rid;
    }

    /**
     * @param string $uid user ID
     * @return string user refresh tokens collection Key
     * @since XXX
     */
    public function getUserRefreshTokensKey($uid)
    {
        return $this->userNamespace . ':' . $uid . ':refreshTokens';
    }

    /**
     * @param string $cid client ID
     * @return string client refresh tokens collection Key
     * @since XXX
     */
    public function getClientRefreshTokensKey($cid)
    {
        return $this->clientNamespace . ':' . $cid . ':refreshTokens';
    }

    /**
     * @return string key of all refresh tokens list
     */
    public function getRefreshTokenListKey()
    {
        return $this->namespace . ':keys';
    }

    /**
     * @return string key of all users list
     */
    public function getUserListKey()
    {
        return $this->userNamespace . ':keys';
    }

    /**
     * @inheritdoc
     */
    public function save(RefreshTokenModelInterface $refreshToken, $attributes)
    {
        if ($refreshToken->getIsNewRecord()) {
            $result = $this->insert($refreshToken, $attributes);
        } else {
            $result = $this->update($refreshToken, $attributes);
        }
        return $result;
    }

    /**
     * Save Refresh Token
     * @param RefreshTokenModelInterface $refreshToken
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     * @since 1.0.0
     */
    protected function insert(RefreshTokenModelInterface $refreshToken, $attributes)
    {
        $result = false;
        if (!$refreshToken->beforeSave(true)) {
            return $result;
        }
        $refreshTokenId = $refreshToken->getKey();
        $refreshTokenKey = $this->getRefreshTokenKey($refreshTokenId);
        if (empty($refreshToken->userId) === false) {
            $userRefreshTokensKey = $this->getUserRefreshTokensKey($refreshToken->userId);
        } else {
            $userRefreshTokensKey = null;
        }
        $clientRefreshTokensKey = $this->getClientRefreshTokensKey($refreshToken->clientId);
        $refreshTokenListKey = $this->getRefreshTokenListKey();
        $userListKey = $this->getUserListKey();

        //check if record exists
        $entityStatus = (int)$this->db->executeCommand('EXISTS', [$refreshTokenKey]);
        if ($entityStatus === 1) {
            throw new DuplicateKeyException('Duplicate key "'.$refreshTokenKey.'"');
        }

        $values = $refreshToken->getDirtyAttributes($attributes);
        $redisParameters = [$refreshTokenKey];
        $this->setAttributesDefinitions($refreshToken->attributesDefinition());
        $expire = $refreshToken->expiry;
        foreach ($values as $key => $value)
        {
            if (($key === 'expiry') && ($value > 0)) {
                $expire = $value;
            }
            if ($value !== null) {
                $redisParameters[] = $key;
                $redisParameters[] = $this->convertToDatabase($key, $value);
            }
        }
        //TODO: use EXEC/MULTI to avoid errors
        $transaction = $this->db->executeCommand('MULTI');
        if ($transaction === true) {
            try {
                $this->db->executeCommand('HMSET', $redisParameters);
                if ($expire !== null) {
                    $this->db->executeCommand('EXPIREAT', [$refreshTokenKey, $expire]);
                }
                if ($userRefreshTokensKey !== null) {
                    $this->db->executeCommand('ZADD', [$userRefreshTokensKey, $expire === false ? -1 : $expire, $refreshTokenId]);
                }
                $this->db->executeCommand('ZADD', [$clientRefreshTokensKey, $expire === false ? -1 : $expire, $refreshTokenId]);
                $this->db->executeCommand('ZADD', [$refreshTokenListKey, $expire === false ? -1 : $expire, $refreshTokenId]);
                $this->db->executeCommand('SADD', [$userListKey, $refreshToken->userId]);
                $this->db->executeCommand('EXEC');
            } catch (DatabaseException $e) {
                // @codeCoverageIgnoreStart
                // we have a REDIS exception, we should not discard
                Yii::debug('Error while inserting entity', __METHOD__);
                throw $e;
                // @codeCoverageIgnoreEnd
            }
        }
        $changedAttributes = array_fill_keys(array_keys($values), null);
        $refreshToken->setOldAttributes($values);
        $refreshToken->afterSave(true, $changedAttributes);
        $result = true;
        return $result;
    }


    /**
     * Update Refresh Token
     * @param RefreshTokenModelInterface $refreshToken
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     */
    protected function update(RefreshTokenModelInterface $refreshToken, $attributes)
    {
        if (!$refreshToken->beforeSave(false)) {
            return false;
        }

        $values = $refreshToken->getDirtyAttributes($attributes);
        $modelKey = $refreshToken->key();
        $refreshTokenId = isset($values[$modelKey]) ? $values[$modelKey] : $refreshToken->getKey();
        $refreshTokenKey = $this->getRefreshTokenKey($refreshTokenId);
        $refreshTokenListKey = $this->getRefreshTokenListKey();
        $userListKey = $this->getUserListKey();

        if (empty($refreshToken->userId) === false) {
            $userRefreshTokensKey = $this->getUserRefreshTokensKey($refreshToken->userId);
        } else {
            $userRefreshTokensKey = null;
        }
        $clientRefreshTokensKey = $this->getClientRefreshTokensKey($refreshToken->clientId);

        if (isset($values[$modelKey]) === true) {
            $newRefreshTokenKey = $this->getRefreshTokenKey($values[$modelKey]);
            $entityStatus = (int)$this->db->executeCommand('EXISTS', [$newRefreshTokenKey]);
            if ($entityStatus === 1) {
                throw new DuplicateKeyException('Duplicate key "'.$newRefreshTokenKey.'"');
            }
        }

        $this->db->executeCommand('MULTI');
        try {
            $reAddKeyInList = false;
            if (array_key_exists($modelKey, $values) === true) {
                $oldId = $refreshToken->getOldKey();
                $oldRefreshTokenKey = $this->getRefreshTokenKey($oldId);

                $this->db->executeCommand('RENAMENX', [$oldRefreshTokenKey, $refreshTokenKey]);
                if ($userRefreshTokensKey !== null) {
                    $this->db->executeCommand('ZREM', [$userRefreshTokensKey, $oldRefreshTokenKey]);
                }
                $this->db->executeCommand('ZREM', [$clientRefreshTokensKey, $oldRefreshTokenKey]);
                $this->db->executeCommand('ZREM', [$refreshTokenListKey, $oldRefreshTokenKey]);
                $reAddKeyInList = true;
            }

            $redisUpdateParameters = [$refreshTokenKey];
            $redisDeleteParameters = [$refreshTokenKey];
            $this->setAttributesDefinitions($refreshToken->attributesDefinition());
            $expire = $refreshToken->expiry;
            foreach ($values as $key => $value)
            {
                if ($value === null) {
                    if ($key === 'expiry') {
                        $expire = false;
                    }
                    $redisDeleteParameters[] = $key;
                } else {
                    if (($key === 'expiry') && ($value > 0)) {
                        $expire = $value;
                    }
                    $redisUpdateParameters[] = $key;
                    $redisUpdateParameters[] = $this->convertToDatabase($key, $value);
                }
            }
            if (count($redisDeleteParameters) > 1) {
                $this->db->executeCommand('HDEL', $redisDeleteParameters);
            }
            if (count($redisUpdateParameters) > 1) {
                $this->db->executeCommand('HMSET', $redisUpdateParameters);
            }
            if ($expire === false) {
                $this->db->executeCommand('PERSIST', [$refreshTokenKey]);
            } elseif ($expire > 0) {
                $this->db->executeCommand('EXPIREAT', [$refreshTokenKey, $expire]);
            }

            if ($reAddKeyInList === true) {
                if ($userRefreshTokensKey !== null) {
                    $this->db->executeCommand('ZADD', [$userRefreshTokensKey, $expire === false ? -1 : $expire, $refreshTokenKey]);
                }
                $this->db->executeCommand('ZADD', [$clientRefreshTokensKey, $expire === false ? -1 : $expire, $refreshTokenKey]);
                $this->db->executeCommand('ZADD', [$refreshTokenListKey, $expire === false ? -1 : $expire, $refreshTokenKey]);
            }
            $this->db->executeCommand('SADD', [$userListKey, $refreshToken->userId]);

            $this->db->executeCommand('EXEC');
        } catch (DatabaseException $e) {
            // @codeCoverageIgnoreStart
            // we have a REDIS exception, we should not discard
            Yii::debug('Error while updating entity', __METHOD__);
            throw $e;
            // @codeCoverageIgnoreEnd
        }

        $changedAttributes = [];
        foreach ($values as $name => $value) {
            $oldAttributes = $refreshToken->getOldAttributes();
            $changedAttributes[$name] = isset($oldAttributes[$name]) ? $oldAttributes[$name] : null;
            $refreshToken->setOldAttribute($name, $value);
        }
        $refreshToken->afterSave(false, $changedAttributes);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findOne($key)
    {
        $record = null;
        $refreshTokenKey = $this->getRefreshTokenKey($key);
        $refreshTokenExists = (bool)$this->db->executeCommand('EXISTS', [$refreshTokenKey]);
        if ($refreshTokenExists === true) {
            $refreshTokenData = $this->db->executeCommand('HGETALL', [$refreshTokenKey]);
            $record = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
            /** @var RefreshTokenModelInterface $record */
            $properties = $record->attributesDefinition();
            $this->setAttributesDefinitions($properties);
            $attributes = [];
            for ($i = 0; $i < count($refreshTokenData); $i += 2) {
                if (isset($properties[$refreshTokenData[$i]]) === true) {
                    $refreshTokenData[$i + 1] = $this->convertToModel($refreshTokenData[$i], $refreshTokenData[($i + 1)]);
                    $record->setAttribute($refreshTokenData[$i], $refreshTokenData[$i + 1]);
                    $attributes[$refreshTokenData[$i]] = $refreshTokenData[$i + 1];
                // @codeCoverageIgnoreStart
                } elseif ($record->canSetProperty($refreshTokenData[$i])) {
                    // TODO: find a way to test attribute population
                    $record->{$refreshTokenData[$i]} = $refreshTokenData[$i + 1];
                }
                // @codeCoverageIgnoreEnd
            }
            if (empty($attributes) === false) {
                $record->setOldAttributes($attributes);
            }
            $record->afterFind();
        }
        return $record;
    }

    /**
     * @inheritdoc
     */
    public function findAllByUserId($userId)
    {
        $userRefreshTokensKey = $this->getUserRefreshTokensKey($userId);
        $userRefreshTokens = $this->db->executeCommand('ZRANGE', [$userRefreshTokensKey, 0, -1]);
        $refreshTokens = [];
        if ((is_array($userRefreshTokens) === true) && (count($userRefreshTokens) > 0)) {
            foreach($userRefreshTokens as $userRefreshTokenId) {
                $refreshTokens[] = $this->findOne($userRefreshTokenId);
            }
        }
        return $refreshTokens;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllByUserId($userId)
    {
        $userRefreshTokensKey = $this->getUserRefreshTokensKey($userId);
        $userRefreshTokens = $this->db->executeCommand('ZRANGE', [$userRefreshTokensKey, 0, -1]);
        foreach ($userRefreshTokens as $userRefreshTokenId) {
            $userRefreshToken = $this->findOne($userRefreshTokenId);
            if ($userRefreshToken instanceof RefreshTokenModelInterface) {
                $this->delete($userRefreshToken);
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findAllByClientId($clientId)
    {
        $clientRefreshTokensKey = $this->getClientRefreshTokensKey($clientId);
        $clientRefreshTokens = $this->db->executeCommand('ZRANGE', [$clientRefreshTokensKey, 0, -1]);
        $refreshTokens = [];
        if ((is_array($clientRefreshTokens) === true) && (count($clientRefreshTokens) > 0)) {
            foreach($clientRefreshTokens as $clientRefreshTokenId) {
                $refreshTokens[] = $this->findOne($clientRefreshTokenId);
            }
        }
        return $refreshTokens;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllByClientId($clientId)
    {
        $clientRefreshTokensKey = $this->getClientRefreshTokensKey($clientId);
        $clientRefreshTokens = $this->db->executeCommand('ZRANGE', [$clientRefreshTokensKey, 0, -1]);
        foreach ($clientRefreshTokens as $clientRefreshTokenId) {
            $clientRefreshToken = $this->findOne($clientRefreshTokenId);
            if ($clientRefreshToken instanceof RefreshTokenModelInterface) {
                $this->delete($clientRefreshToken);
            }
        }
        return true;
    }


    /**
     * @inheritdoc
     */
    public function delete(RefreshTokenModelInterface $refreshToken)
    {
        $result = false;
        if ($refreshToken->beforeDelete()) {
            if (empty($refreshToken->userId) === false) {
                $userRefreshTokensKey = $this->getUserRefreshTokensKey($refreshToken->userId);
            } else {
                $userRefreshTokensKey = null;
            }
            $clientRefreshTokensKey = $this->getClientRefreshTokensKey($refreshToken->clientId);
            $refreshTokenListKey = $this->getRefreshTokenListKey();

            $this->db->executeCommand('MULTI');
            $id = $refreshToken->getOldKey();
            $refreshTokenKey = $this->getRefreshTokenKey($id);

            $this->db->executeCommand('DEL', [$refreshTokenKey]);
            if ($userRefreshTokensKey !== null) {
                $this->db->executeCommand('ZREM', [$userRefreshTokensKey, $id]);
            }
            $this->db->executeCommand('ZREM', [$clientRefreshTokensKey, $id]);
            $this->db->executeCommand('ZREM', [$refreshTokenListKey, $id]);
            //TODO: check results to return correct information
            $queryResult = $this->db->executeCommand('EXEC');
            $refreshToken->setIsNewRecord(true);
            $refreshToken->afterDelete();
            $result = true;
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllExpired()
    {
        $date = time();
        $refreshTokenListKey = $this->getRefreshTokenListKey();
        $this->db->executeCommand('ZREMRANGEBYSCORE', [$refreshTokenListKey, -1, $date]);

        $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        $clientClass = get_class($client);
        /* @var \sweelix\oauth2\server\interfaces\ClientModelInterface[] $clientList */
        $clientList = $clientClass::findAll();
        foreach ($clientList as $client) {
            $clientRefreshTokensKey = $this->getClientRefreshTokensKey($client->getKey());
            $this->db->executeCommand('ZREMRANGEBYSCORE', [$clientRefreshTokensKey, -1, $date]);
        }

        $userListKey = $this->getUserListKey();
        $users = $this->db->executeCommand('SMEMBERS', [$userListKey]);
        foreach ($users as $userId) {
            $userRefreshTokensKey = $this->getUserRefreshTokensKey($userId);
            $this->db->executeCommand('ZREMRANGEBYSCORE', [$userRefreshTokensKey, '-inf', $date]);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        $refreshTokenListKey = $this->getRefreshTokenListKey();
        $refreshTokenList = $this->db->executeCommand('ZRANGE', [$refreshTokenListKey, 0, -1]);
        $refreshTokens = [];
        if ((is_array($refreshTokenList) === true) && (count($refreshTokenList) > 0)) {
            foreach ($refreshTokenList as $refreshTokenId) {
                $refreshTokens[] = $this->findOne($refreshTokenId);
            }
        }
        return $refreshTokens;
    }
}
