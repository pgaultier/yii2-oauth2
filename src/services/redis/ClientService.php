<?php
/**
 * ClientService.php
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
use sweelix\oauth2\server\interfaces\ClientModelInterface;
use sweelix\oauth2\server\interfaces\ClientServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;

/**
 * This is the client service for redis
 *  database structure
 *    * oauth2:clients:<cid> : hash (Client)
 *    * oauth2:clients:<cid>:users : set
 *    * oauth2:users:<uid>:clients : set
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\redis
 * @since 1.0.0
 */
class ClientService extends BaseService implements ClientServiceInterface
{

    /**
     * @var string user namespace (collection for clients)
     */
    public $userNamespace = '';

    /**
     * @param string $cid client ID
     * @return string client Key
     * @since 1.0.0
     */
    protected function getClientKey($cid)
    {
        return $this->namespace . ':' . $cid;
    }

    /**
     * @param string $cid client ID
     * @return string clientUsers Key
     * @since 1.0.0
     */
    protected function getClientUsersListKey($cid)
    {
        return $this->namespace . ':' . $cid . ':users';
    }

    /**
     * @param string $uid user ID
     * @return string user clients collection Key
     * @since XXX
     */
    protected function getUserClientsListKey($uid)
    {
        return $this->userNamespace . ':' . $uid . ':clients';
    }

    /**
     * @inheritdoc
     */
    public function save(ClientModelInterface $client, $attributes)
    {
        if ($client->getIsNewRecord()) {
            $result = $this->insert($client, $attributes);
        } else {
            $result = $this->update($client, $attributes);
        }
        return $result;
    }

    /**
     * Save Client
     * @param ClientModelInterface $client
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     * @since 1.0.0
     */
    protected function insert(ClientModelInterface $client, $attributes)
    {
        $result = false;
        if (!$client->beforeSave(true)) {
            return $result;
        }
        $clientKey = $this->getClientKey($client->getKey());
        //check if record exists
        $entityStatus = (int)$this->db->executeCommand('EXISTS', [$clientKey]);
        if ($entityStatus === 1) {
            throw new DuplicateKeyException('Duplicate key "'.$clientKey.'"');
        }

        $values = $client->getDirtyAttributes($attributes);
        $redisParameters = [$clientKey];
        $this->setAttributesDefinitions($client->attributesDefinition());
        foreach ($values as $key => $value)
        {
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
        $client->setOldAttributes($values);
        $client->afterSave(true, $changedAttributes);
        $result = true;
        return $result;
    }


    /**
     * Update Client
     * @param ClientModelInterface $client
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     */
    protected function update(ClientModelInterface $client, $attributes)
    {
        if (!$client->beforeSave(false)) {
            return false;
        }

        $values = $client->getDirtyAttributes($attributes);
        $modelKey = $client->key();
        $clientId = isset($values[$modelKey]) ? $values[$modelKey] : $client->getKey();
        $clientKey = $this->getClientKey($clientId);


        if (isset($values[$modelKey]) === true) {
            $newClientKey = $this->getClientKey($values[$modelKey]);
            $entityStatus = (int)$this->db->executeCommand('EXISTS', [$newClientKey]);
            if ($entityStatus === 1) {
                throw new DuplicateKeyException('Duplicate key "'.$newClientKey.'"');
            }
        }

        $this->db->executeCommand('MULTI');
        try {
            if (array_key_exists($modelKey, $values) === true) {
                $oldId = $client->getOldKey();
                $oldClientKey = $this->getClientKey($oldId);

                $this->db->executeCommand('RENAMENX', [$oldClientKey, $clientKey]);
            }

            $redisUpdateParameters = [$clientKey];
            $redisDeleteParameters = [$clientKey];
            $this->setAttributesDefinitions($client->attributesDefinition());
            foreach ($values as $key => $value)
            {
                if ($value === null) {
                    $redisDeleteParameters[] = $key;
                } else {
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
            $oldAttributes = $client->getOldAttributes();
            $changedAttributes[$name] = isset($oldAttributes[$name]) ? $oldAttributes[$name] : null;
            $client->setOldAttribute($name, $value);
        }
        $client->afterSave(false, $changedAttributes);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findOne($key)
    {
        $record = null;
        $clientKey = $this->getClientKey($key);
        $clientExists = (bool)$this->db->executeCommand('EXISTS', [$clientKey]);
        if ($clientExists === true) {
            $clientData = $this->db->executeCommand('HGETALL', [$clientKey]);
            $record = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
            /** @var ClientModelInterface $record */
            $properties = $record->attributesDefinition();
            $this->setAttributesDefinitions($properties);
            $attributes = [];
            for ($i = 0; $i < count($clientData); $i += 2) {
                if (isset($properties[$clientData[$i]]) === true) {
                    $clientData[$i + 1] = $this->convertToModel($clientData[$i], $clientData[($i + 1)]);
                    $record->setAttribute($clientData[$i], $clientData[$i + 1]);
                    $attributes[$clientData[$i]] = $clientData[$i + 1];
                // @codeCoverageIgnoreStart
                } elseif ($record->canSetProperty($clientData[$i])) {
                    // TODO: find a way to test attribute population
                    $record->{$clientData[$i]} = $clientData[$i + 1];
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
    public function delete(ClientModelInterface $client)
    {
        $result = false;
        if ($client->beforeDelete()) {
            $id = $client->getOldKey();
            $clientKey = $this->getClientKey($id);
            $clientUsersListKey = $this->getClientUsersListKey($id);


            // before cleaning the client, drop all access tokens and refresh tokens
            $token = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
            $tokenClass = get_class($token);
            $tokenClass::deleteAllByClientId($id);

            $token = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
            $tokenClass = get_class($token);
            $tokenClass::deleteAllByClientId($id);

            $usersList = $this->db->executeCommand('SMEMBERS', [$clientUsersListKey]);

            $this->db->executeCommand('MULTI');
            // remove client from all userClient sets
            foreach($usersList as $user) {
                $userClientKey = $this->getUserClientsListKey($user);
                $this->db->executeCommand('SREM', [$userClientKey, $id]);
            }
            $this->db->executeCommand('DEL', [$clientKey]);
            $this->db->executeCommand('DEL', [$clientUsersListKey]);
            //TODO: check results to return correct information
            $queryResult = $this->db->executeCommand('EXEC');
            $client->setIsNewRecord(true);
            $client->afterDelete();
            $result = true;
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function hasUser(ClientModelInterface $client, $userId)
    {
        $key = $client->getKey();
        $clientUsersListKey = $this->getClientUsersListKey($key);
        return (bool)$this->db->executeCommand('SISMEMBER', [$clientUsersListKey, $userId]);
    }

    /**
     * @inheritdoc
     */
    public function addUser(ClientModelInterface $client, $userId)
    {
        $key = $client->getKey();
        $clientUsersListKey = $this->getClientUsersListKey($key);
        $userClientsListKey = $this->getUserClientsListKey($userId);
        $this->db->executeCommand('MULTI');
        $this->db->executeCommand('SADD', [$clientUsersListKey, $userId]);
        $this->db->executeCommand('SADD', [$userClientsListKey, $key]);
        $queryResult = $this->db->executeCommand('EXEC');
        //TODO: check if we should send back false or not
        return true;
    }

    /**
     * @inheritdoc
     */
    public function removeUser(ClientModelInterface $client, $userId)
    {
        $key = $client->getKey();
        $clientUsersListKey = $this->getClientUsersListKey($key);
        $userClientsListKey = $this->getUserClientsListKey($userId);
        $this->db->executeCommand('MULTI');
        $this->db->executeCommand('SREM', [$clientUsersListKey, $userId]);
        $this->db->executeCommand('SREM', [$userClientsListKey, $key]);
        $queryResult = $this->db->executeCommand('EXEC');
        //TODO: check if we should send back false or not
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findAllByUserId($userId)
    {
        $userClientsListKey = $this->getUserClientsListKey($userId);
        $clientsList = $this->db->executeCommand('SMEMBERS', [$userClientsListKey]);
        $clients = [];
        foreach($clientsList as $clientId) {
            $result = $this->findOne($clientId);
            if ($result instanceof ClientModelInterface) {
                $clients[] = $result;
            }
        }
        return $clients;
    }

}
