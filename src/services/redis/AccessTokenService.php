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

        //check if record exists
        $entityStatus = (int)$this->db->executeCommand('EXISTS', [$accessTokenKey]);
        if ($entityStatus === 1) {
            throw new DuplicateKeyException('Duplicate key "'.$accessTokenKey.'"');
        }

        $values = $accessToken->getDirtyAttributes($attributes);
        $redisParameters = [$accessTokenKey];
        $this->setAttributesDefinitions($accessToken->attributesDefinition());
        $expire = null;
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
                    $this->db->executeCommand('EXPIREAT', [$accessTokenKey, $expire]);
                }
                if ($userAccessTokensKey !== null) {
                    $this->db->executeCommand('SADD', [$userAccessTokensKey, $accessTokenId]);
                }
                $this->db->executeCommand('SADD', [$clientAccessTokensKey, $accessTokenId]);
                $this->db->executeCommand('EXEC');
            } catch (DatabaseException $e) {
                // @codeCoverageIgnoreStart
                // we have a REDIS exception, we should not discard
                Yii::trace('Error while inserting entity', __METHOD__);
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
                throw new DuplicateKeyException('Duplicate key "'.$newAccessTokenKey.'"');
            }
        }

        $this->db->executeCommand('MULTI');
        try {
            if (array_key_exists($modelKey, $values) === true) {
                $oldId = $accessToken->getOldKey();
                $oldAccessTokenKey = $this->getAccessTokenKey($oldId);
                $this->db->executeCommand('RENAMENX', [$oldAccessTokenKey, $accessTokenKey]);
                if ($userAccessTokensKey !== null) {
                    $this->db->executeCommand('SREM', [$userAccessTokensKey, $oldAccessTokenKey]);
                    $this->db->executeCommand('SADD', [$userAccessTokensKey, $accessTokenKey]);
                }
                $this->db->executeCommand('SREM', [$clientAccessTokensKey, $oldAccessTokenKey]);
                $this->db->executeCommand('SADD', [$clientAccessTokensKey, $accessTokenKey]);
            }

            $redisUpdateParameters = [$accessTokenKey];
            $redisDeleteParameters = [$accessTokenKey];
            $this->setAttributesDefinitions($accessToken->attributesDefinition());
            $expire = null;
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
                $this->db->executeCommand('PERSIST', [$accessTokenKey]);
            } elseif ($expire > 0) {
                $this->db->executeCommand('EXPIREAT', [$accessTokenKey, $expire]);
            }

            $this->db->executeCommand('EXEC');
        } catch (DatabaseException $e) {
            // @codeCoverageIgnoreStart
            // we have a REDIS exception, we should not discard
            Yii::trace('Error while updating entity', __METHOD__);
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
        $userAccessTokens = $this->db->executeCommand('SMEMBERS', [$userAccessTokensKey]);
        $accessTokens = [];
        if ((is_array($userAccessTokens) === true) && (count($userAccessTokens) > 0)) {
            foreach($userAccessTokens as $userAccessTokenId) {
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
        $userAccessTokens = $this->db->executeCommand('SMEMBERS', [$userAccessTokensKey]);
        $userAccessTokenKeys = [$userAccessTokensKey];
        foreach ($userAccessTokens as $userAccessToken) {
            $userAccessTokenKeys[] = $this->getAccessTokenKey($userAccessToken);
        }
        $this->db->executeCommand('DEL', $userAccessTokenKeys);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findAllByClientId($clientId)
    {
        $clientAccessTokensKey = $this->getClientAccessTokensKey($clientId);
        $clientAccessTokens = $this->db->executeCommand('SMEMBERS', [$clientAccessTokensKey]);
        $accessTokens = [];
        if ((is_array($clientAccessTokens) === true) && (count($clientAccessTokens) > 0)) {
            foreach($clientAccessTokens as $clientAccessTokenId) {
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
        $clientAccessTokens = $this->db->executeCommand('SMEMBERS', [$clientAccessTokensKey]);
        $clientAccessTokenKeys = [$clientAccessTokensKey];
        foreach ($clientAccessTokens as $clientAccessToken) {
            $clientAccessTokenKeys[] = $this->getAccessTokenKey($clientAccessToken);
        }
        $this->db->executeCommand('DEL', $clientAccessTokenKeys);
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
            $clientAccessTokensKey = $this->getClientAccessTokensKey($accessToken->userId);

            $this->db->executeCommand('MULTI');
            $id = $accessToken->getOldKey();
            $accessTokenKey = $this->getAccessTokenKey($id);

            $this->db->executeCommand('DEL', [$accessTokenKey]);
            if ($userAccessTokensKey !== null) {
                $this->db->executeCommand('SREM', [$userAccessTokensKey, $id]);
            }
            $this->db->executeCommand('SREM', [$clientAccessTokensKey, $id]);
            //TODO: check results to return correct information
            $queryResult = $this->db->executeCommand('EXEC');
            $accessToken->setIsNewRecord(true);
            $accessToken->afterDelete();
            $result = true;
        }
        return $result;
    }

}
