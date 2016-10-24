<?php
/**
 * ClientService.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\redis
 */

namespace sweelix\oauth2\server\services\redis;

use sweelix\oauth2\server\exceptions\DuplicateIndexException;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
use sweelix\oauth2\server\models\Client;
use sweelix\oauth2\server\interfaces\ClientServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;

/**
 * This is the client service for redis
 *  database structure
 *    * oauth2:clients:<cid> : hash (Client)
 *    * oauth2:clients:etags : hash <cid> -> <etag>
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\redis
 * @since XXX
 */
class ClientService extends BaseService implements ClientServiceInterface
{

    /**
     * @param string $cid client ID
     * @return string client Key
     * @since XXX
     */
    protected function getClientKey($cid)
    {
        return $this->namespace . ':' . $cid;
    }

    /**
     * @return string etag index Key
     * @since XXX
     */
    protected function getEtagIndexKey()
    {
        return $this->namespace . ':etags';
    }

    /**
     * @inheritdoc
     */
    public function save(Client $client, $attributes)
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
     * @param Client $client
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     * @since XXX
     */
    protected function insert(Client $client, $attributes)
    {
        $result = false;
        if (!$client->beforeSave(true)) {
            return $result;
        }
        $clientKey = $this->getClientKey($client->id);
        $etagKey = $this->getEtagIndexKey();
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
                $etag = $this->computeEtag($client);
                $this->db->executeCommand('HSET', [$etagKey, $client->id, $etag]);
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
     * @param Client $client
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     */
    protected function update(Client $client, $attributes)
    {
        if (!$client->beforeSave(false)) {
            return false;
        }

        $etagKey = $this->getEtagIndexKey();
        $values = $client->getDirtyAttributes($attributes);
        $clientId = isset($values['id']) ? $values['id'] : $client->id;
        $clientKey = $this->getClientKey($clientId);


        if (isset($values['id']) === true) {
            $newClientKey = $this->getClientKey($values['id']);
            $entityStatus = (int)$this->db->executeCommand('EXISTS', [$newClientKey]);
            if ($entityStatus === 1) {
                throw new DuplicateKeyException('Duplicate key "'.$newClientKey.'"');
            }
        }

        $this->db->executeCommand('MULTI');
        try {
            if (array_key_exists('id', $values) === true) {
                $oldId = $client->getOldAttribute('id');
                $oldClientKey = $this->getClientKey($oldId);

                $this->db->executeCommand('RENAMENX', [$oldClientKey, $clientKey]);
                $this->db->executeCommand('HDEL', [$etagKey, $oldClientKey]);
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

            $etag = $this->computeEtag($client);
            $this->db->executeCommand('HSET', [$etagKey, $clientId, $etag]);

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
            $record = Yii::createObject(Client::className());
            /** @var Client $record */
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
    public function delete(Client $client)
    {
        $result = false;
        if ($client->beforeDelete()) {
            $etagKey = $this->getEtagIndexKey();
            $this->db->executeCommand('MULTI');
            $id = $client->getOldKey();
            $clientKey = $this->getClientKey($id);

            $this->db->executeCommand('HDEL', [$etagKey, $id]);
            $this->db->executeCommand('DEL', [$clientKey]);
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
    public function getEtag($key)
    {
        return $this->db->executeCommand('HGET', [$this->getEtagIndexKey(), $key]);
    }

}
