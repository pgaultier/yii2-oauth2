<?php
/**
 * AccessTokenService.php
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
use sweelix\oauth2\server\interfaces\AccessTokenModelInterface;
use sweelix\oauth2\server\interfaces\AccessTokenServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;

/**
 * This is the access token service for redis
 *  database structure
 *    * oauth2:accessTokens:<aid> : hash (AccessToken)
 *    * oauth2:users:<uid>:accessTokens : set (AccessToken for user)
 *    * oauth2:clients:<cid>:accessTokens : set (AccessToken for client)
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\redis
 * @since 1.0.0
 */
class AccessTokenService extends BaseService implements AccessTokenServiceInterface
{
    /**
     * @var string user namespace (collection for accesstokens)
     */
    public $userNamespace = '';

    /**
     * @var string client namespace (collection for accesstokens)
     */
    public $clientNamespace = '';

    /**
     * @param string $aid access token ID
     * @return string access token Key
     * @since 1.0.0
     */
    protected function getAccessTokenKey($aid)
    {
        return $this->namespace . ':' . $aid;
    }

    /**
     * @param string $uid user ID
     * @return string user access tokens collection Key
     * @since XXX
     */
    protected function getUserAccessTokensKey($uid)
    {
        return $this->userNamespace . ':' . $uid . ':accessTokens';
    }

    /**
     * @param string $cid client ID
     * @return string client access tokens collection Key
     * @since XXX
     */
    protected function getClientAccessTokensKey($cid)
    {
        return $this->clientNamespace . ':' . $cid . ':accessTokens';
    }

    /**
     * @return string key of all access tokens list
     */
    protected function getAccessTokenListKey()
    {
        return $this->namespace . ':keys';
    }

    /**
     * @return string key of all users list
     */
    protected function getUserListKey()
    {
        return $this->userNamespace . ':keys';
    }

    /**
     * @inheritdoc
     */
    public function save(AccessTokenModelInterface $accessToken, $attributes)
    {
        if ($accessToken->getIsNewRecord()) {
            $result = $this->insert($accessToken, $attributes);
        } else {
            $result = $this->update($accessToken, $attributes);
        }
        return $result;
    }

    /**
     * Save Access Token
     * @param AccessTokenModelInterface $accessToken
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     * @since 1.0.0
     */
    protected function insert(AccessTokenModelInterface $accessToken, $attributes)
    {
        $result = false;
        if (!$accessToken->beforeSave(true)) {
            return $result;
        }
        $accessTokenId = $accessToken->getKey();
        $accessTokenKey = $this->getAccessTokenKey($accessTokenId);
        if (empty($accessToken->userId) === false) {
            $userAccessTokensKey = $this->getUserAccessTokensKey($accessToken->userId);
        } else {
            $userAccessTokensKey = null;
        }
        $clientAccessTokensKey = $this->getClientAccessTokensKey($accessToken->clientId);
        $accessTokenListKey = $this->getAccessTokenListKey();
        $userListKey = $this->getUserListKey();

        //check if record exists
        $entityStatus = (int)$this->db->executeCommand('EXISTS', [$accessTokenKey]);
        if ($entityStatus === 1) {
            throw new DuplicateKeyException('Duplicate key "' . $accessTokenKey . '"');
        }

        $values = $accessToken->getDirtyAttributes($attributes);
        $redisParameters = [$accessTokenKey];
        $this->setAttributesDefinitions($accessToken->attributesDefinition());
        $expire = null;
        foreach ($values as $key => $value) {
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
                    $this->db->executeCommand('EXPIREAT', [$accessTokenKey, $expire]);
                }
                if ($userAccessTokensKey !== null) {
                    $this->db->executeCommand('ZADD', [$userAccessTokensKey, $expire === false ? -1 : $expire, $accessTokenId]);
                }
                $this->db->executeCommand('ZADD', [$clientAccessTokensKey, $expire === false ? -1 : $expire, $accessTokenId]);
                $this->db->executeCommand('ZADD', [$accessTokenListKey, $expire === false ? -1 : $expire, $accessTokenId]);
                $this->db->executeCommand('SADD', [$userListKey, $accessToken->userId]);
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
        $accessToken->setOldAttributes($values);
        $accessToken->afterSave(true, $changedAttributes);
        $result = true;
        return $result;
    }


    /**
     * Update Access Token
     * @param AccessTokenModelInterface $accessToken
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     */
    protected function update(AccessTokenModelInterface $accessToken, $attributes)
    {
        if (!$accessToken->beforeSave(false)) {
            return false;
        }

        $values = $accessToken->getDirtyAttributes($attributes);
        $modelKey = $accessToken->key();
        $accessTokenId = isset($values[$modelKey]) ? $values[$modelKey] : $accessToken->getKey();
        $accessTokenKey = $this->getAccessTokenKey($accessTokenId);
        $accessTokenListKey = $this->getAccessTokenListKey();
        $userListKey = $this->getUserListKey();

        if (empty($accessToken->userId) === false) {
            $userAccessTokensKey = $this->getUserAccessTokensKey($accessToken->userId);
        } else {
            $userAccessTokensKey = null;
        }
        $clientAccessTokensKey = $this->getClientAccessTokensKey($accessToken->clientId);

        if (isset($values[$modelKey]) === true) {
            $newAccessTokenKey = $this->getAccessTokenKey($values[$modelKey]);
            $entityStatus = (int)$this->db->executeCommand('EXISTS', [$newAccessTokenKey]);
            if ($entityStatus === 1) {
                throw new DuplicateKeyException('Duplicate key "' . $newAccessTokenKey . '"');
            }
        }

        $this->db->executeCommand('MULTI');
        try {
            $reAddKeyInList = false;
            if (array_key_exists($modelKey, $values) === true) {
                $oldId = $accessToken->getOldKey();
                $oldAccessTokenKey = $this->getAccessTokenKey($oldId);
                $this->db->executeCommand('RENAMENX', [$oldAccessTokenKey, $accessTokenKey]);
                if ($userAccessTokensKey !== null) {
                    $this->db->executeCommand('ZREM', [$userAccessTokensKey, $oldAccessTokenKey]);
                }
                $this->db->executeCommand('ZREM', [$clientAccessTokensKey, $oldAccessTokenKey]);
                $this->db->executeCommand('ZREM', [$accessTokenListKey, $oldAccessTokenKey]);
                $reAddKeyInList = true;
            }

            $redisUpdateParameters = [$accessTokenKey];
            $redisDeleteParameters = [$accessTokenKey];
            $this->setAttributesDefinitions($accessToken->attributesDefinition());
            $expire = $accessToken->expiry;
            foreach ($values as $key => $value) {
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
                $this->db->executeCommand('PERSIST', [$accessTokenKey]);
            } elseif ($expire > 0) {
                $this->db->executeCommand('EXPIREAT', [$accessTokenKey, $expire]);
            }

            if ($reAddKeyInList === true) {
                if ($userAccessTokensKey !== null) {
                    $this->db->executeCommand('ZADD', [$userAccessTokensKey, $expire === false ? -1 : $expire, $accessTokenKey]);
                }
                $this->db->executeCommand('ZADD', [$clientAccessTokensKey, $expire === false ? -1 : $expire, $accessTokenKey]);
                $this->db->executeCommand('ZADD', [$accessTokenListKey, $expire === false ? -1 : $expire, $accessTokenKey]);
            }
            $this->db->executeCommand('SADD', [$userListKey, $accessToken->userId]);

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
            $oldAttributes = $accessToken->getOldAttributes();
            $changedAttributes[$name] = isset($oldAttributes[$name]) ? $oldAttributes[$name] : null;
            $accessToken->setOldAttribute($name, $value);
        }
        $accessToken->afterSave(false, $changedAttributes);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findOne($key)
    {
        $record = null;
        $accessTokenKey = $this->getAccessTokenKey($key);
        $accessTokenExists = (bool)$this->db->executeCommand('EXISTS', [$accessTokenKey]);
        if ($accessTokenExists === true) {
            $accessTokenData = $this->db->executeCommand('HGETALL', [$accessTokenKey]);
            $record = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
            /** @var AccessTokenModelInterface $record */
            $properties = $record->attributesDefinition();
            $this->setAttributesDefinitions($properties);
            $attributes = [];
            for ($i = 0; $i < count($accessTokenData); $i += 2) {
                if (isset($properties[$accessTokenData[$i]]) === true) {
                    $accessTokenData[$i + 1] = $this->convertToModel($accessTokenData[$i], $accessTokenData[($i + 1)]);
                    $record->setAttribute($accessTokenData[$i], $accessTokenData[$i + 1]);
                    $attributes[$accessTokenData[$i]] = $accessTokenData[$i + 1];
                    // @codeCoverageIgnoreStart
                } elseif ($record->canSetProperty($accessTokenData[$i])) {
                    // TODO: find a way to test attribute population
                    $record->{$accessTokenData[$i]} = $accessTokenData[$i + 1];
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
        $userAccessTokensKey = $this->getUserAccessTokensKey($userId);
        $userAccessTokens = $this->db->executeCommand('ZRANGE', [$userAccessTokensKey, 0, -1]);
        $accessTokens = [];
        if ((is_array($userAccessTokens) === true) && (count($userAccessTokens) > 0)) {
            foreach ($userAccessTokens as $userAccessTokenId) {
                $accessTokens[] = $this->findOne($userAccessTokenId);
            }
        }
        return $accessTokens;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllByUserId($userId)
    {
        $userAccessTokensKey = $this->getUserAccessTokensKey($userId);
        $userAccessTokens = $this->db->executeCommand('ZRANGE', [$userAccessTokensKey, 0, -1]);
        foreach ($userAccessTokens as $userAccessTokenId) {
            $userAccessToken = $this->findOne($userAccessTokenId);
            if ($userAccessToken instanceof AccessTokenModelInterface) {
                $this->delete($userAccessToken);
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findAllByClientId($clientId)
    {
        $clientAccessTokensKey = $this->getClientAccessTokensKey($clientId);
        $clientAccessTokens = $this->db->executeCommand('ZRANGE', [$clientAccessTokensKey, 0, -1]);
        $accessTokens = [];
        if ((is_array($clientAccessTokens) === true) && (count($clientAccessTokens) > 0)) {
            foreach ($clientAccessTokens as $clientAccessTokenId) {
                $accessTokens[] = $this->findOne($clientAccessTokenId);
            }
        }
        return $accessTokens;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllByClientId($clientId)
    {
        $clientAccessTokensKey = $this->getClientAccessTokensKey($clientId);
        $clientAccessTokens = $this->db->executeCommand('ZRANGE', [$clientAccessTokensKey, 0, -1]);
        foreach ($clientAccessTokens as $clientAccessTokenId) {
            $clientAccessToken = $this->findOne($clientAccessTokenId);
            if ($clientAccessToken instanceof AccessTokenModelInterface) {
                $this->delete($clientAccessToken);
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete(AccessTokenModelInterface $accessToken)
    {
        $result = false;
        if ($accessToken->beforeDelete()) {
            if (empty($accessToken->userId) === false) {
                $userAccessTokensKey = $this->getUserAccessTokensKey($accessToken->userId);
            } else {
                $userAccessTokensKey = null;
            }
            $clientAccessTokensKey = $this->getClientAccessTokensKey($accessToken->clientId);
            $accessTokenListKey = $this->getAccessTokenListKey();

            $this->db->executeCommand('MULTI');
            $id = $accessToken->getOldKey();
            $accessTokenKey = $this->getAccessTokenKey($id);

            $this->db->executeCommand('DEL', [$accessTokenKey]);
            if ($userAccessTokensKey !== null) {
                $this->db->executeCommand('ZREM', [$userAccessTokensKey, $id]);
            }
            $this->db->executeCommand('ZREM', [$clientAccessTokensKey, $id]);
            $this->db->executeCommand('ZREM', [$accessTokenListKey, $id]);
            //TODO: check results to return correct information
            $this->db->executeCommand('EXEC');
            $accessToken->setIsNewRecord(true);
            $accessToken->afterDelete();
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
        $accessTokenListKey = $this->getAccessTokenListKey();
        $this->db->executeCommand('ZREMRANGEBYSCORE', [$accessTokenListKey, '-inf', $date]);

        $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        $clientClass = get_class($client);
        /* @var \sweelix\oauth2\server\interfaces\ClientModelInterface[] $clientList */
        $clientList = $clientClass::findAll();
        foreach ($clientList as $client) {
            $clientAccessTokensKey = $this->getClientAccessTokensKey($client->getKey());
            $this->db->executeCommand('ZREMRANGEBYSCORE', [$clientAccessTokensKey, '-inf', $date]);
        }

        $userListKey = $this->getUserListKey();
        $users = $this->db->executeCommand('SMEMBERS', [$userListKey]);
        foreach ($users as $userId) {
            $userAccessTokensKey = $this->getUserAccessTokensKey($userId);
            $this->db->executeCommand('ZREMRANGEBYSCORE', [$userAccessTokensKey, '-inf', $date]);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        $accessTokenListKey = $this->getAccessTokenListKey();
        $accessTokenList = $this->db->executeCommand('ZRANGE', [$accessTokenListKey, 0, -1]);
        $accessTokens = [];
        if ((is_array($accessTokenList) === true) && (count($accessTokenList) > 0)) {
            foreach ($accessTokenList as $accessTokenId) {
                $accessTokens[] = $this->findOne($accessTokenId);
            }
        }
        return $accessTokens;
    }
}
