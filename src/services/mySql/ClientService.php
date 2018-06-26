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
 * @package sweelix\oauth2\server\services\mySql
 */

namespace sweelix\oauth2\server\services\mySql;

use sweelix\oauth2\server\exceptions\DuplicateIndexException;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
use sweelix\oauth2\server\interfaces\ClientModelInterface;
use sweelix\oauth2\server\interfaces\ClientServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * This is the client service for mySql
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\mySql
 * @since 1.0.0
 */
class ClientService extends BaseService implements ClientServiceInterface
{
    /**
     * @var string sql client grantType junction table
     */
    public $clientGrantTypeTable = null;

    /**
     * @var string sql clients table
     */
    public $clientsTable = null;

    /**
     * @var string sql client user table
     */
    public $clientUserTable = null;

    /**
     * @var string sql scope client junction table
     */
    public $scopeClientTable = null;

    /**
     * @var string sql scopes table
     */
    public $scopesTable = null;

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
        $clientKey = $client->getKey();
        $entity = (new Query())
            ->select('*')
            ->from($this->clientsTable)
            ->where('id = :id', [':id' => $clientKey])
            ->one($this->db);
        if ($entity !== false) {
            throw new DuplicateKeyException('Duplicate key "' . $clientKey . '"');
        }
        $values = $client->getDirtyAttributes($attributes);
        $clientParameters = [];
        $this->setAttributesDefinitions($client->attributesDefinition());
        foreach ($values as $key => $value) {
            if (($value !== null) && ($key !== 'grantTypes') && ($key !== 'scopes')) {
                $clientParameters[$key] = $this->convertToDatabase($key, $value);
            }
        }
        $clientParameters['dateCreated'] = new Expression('NOW()');
        $clientParameters['dateUpdated'] = new Expression('NOW()');
        try {
            $this->db->createCommand()
                ->insert($this->clientsTable, $clientParameters)
                ->execute();
            if (!empty($values['grantTypes'])) {
                $values['grantTypes'] = array_unique($values['grantTypes']);
                foreach ($values['grantTypes'] as $grantType) {
                    $clientGrantTypeParams = [
                        'clientId' => $clientKey,
                        'grantTypeId' => $grantType,
                    ];
                    $this->db->createCommand()
                        ->insert($this->clientGrantTypeTable, $clientGrantTypeParams)
                        ->execute();
                }
            }
            if (!empty($values['scopes'])) {
                $values['scopes'] = array_unique($values['scopes']);
                foreach ($values['scopes'] as $scope) {
                    $scopeClientParams = [
                        'scopeId' => $scope,
                        'clientId' => $clientKey
                    ];
                    $this->db->createCommand()
                        ->insert($this->scopeClientTable, $scopeClientParams)
                        ->execute();
                }
            }
        } catch (DatabaseException $e) {
            // @codeCoverageIgnoreStart
            // we have a MYSQL exception, we should not discard
            Yii::debug('Error while inserting entity', __METHOD__);
            throw $e;
            // @codeCoverageIgnoreEnd
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
        if (isset($values[$modelKey]) === true) {
            $entity = (new Query())
                ->select('*')
                ->from($this->clientsTable)
                ->where('id = :id', [':id' => $values[$modelKey]])
                ->one($this->db);
            if ($entity !== false) {
                throw new DuplicateKeyException('Duplicate key "' . $values[$modelKey] . '"');
            }
        }
        $clientKey = isset($values[$modelKey]) ? $values[$modelKey] : $client->getKey();

        $clientParameters = [];
        $this->setAttributesDefinitions($client->attributesDefinition());
        foreach ($values as $key => $value) {
            if (($key !== 'grantTypes') && ($key !== 'scopes')) {
                $clientParameters[$key] = ($value !== null) ? $this->convertToDatabase($key, $value) : null;
            }
        }
        $clientParameters['dateUpdated'] = new Expression('NOW()');
        try {
            if (array_key_exists($modelKey, $values) === true) {
                $oldClientKey = $client->getOldKey();
                $this->db->createCommand()
                    ->update($this->clientsTable, $clientParameters, 'id = :id', [':id' => $oldClientKey])
                    ->execute();
            } else {
                $this->db->createCommand()
                    ->update($this->clientsTable, $clientParameters, 'id = :id', [':id' => $clientKey])
                    ->execute();
            }
            if (isset($values['grantTypes'])) {
                $values['grantTypes'] = array_unique($values['grantTypes']);
                $clientGrantTypes = (new Query())
                    ->select('*')
                    ->from($this->clientGrantTypeTable)
                    ->where('clientId = :clientId', [':clientId' => $clientKey])
                    ->all($this->db);
                foreach ($clientGrantTypes as $clientGrantType) {
                    if (($index = array_search($clientGrantType['grantTypeId'], $values['grantTypes'])) === false) {
                        $this->db->createCommand()
                            ->delete($this->clientGrantTypeTable,
                                ['clientId = :clientId', 'grantTypeId = :grantTypeId'],
                                [':clientId' => $clientKey, ':grantTypeId' => $clientGrantType['grantTypeId']])
                            ->execute();
                    } else {
                        unset($values['grantTypes'][$index]);
                    }
                }
                foreach ($values['grantTypes'] as $grantType) {
                    $clientGrantTypeParams = [
                        'clientId' => $clientKey,
                        'grantTypeId' => $grantType,
                    ];
                    $this->db->createCommand()
                        ->insert($this->clientGrantTypeTable, $clientGrantTypeParams)
                        ->execute();
                }
            }
            if (isset($values['scopes'])) {
                $values['scopes'] = array_unique($values['scopes']);
                $scopeClients = (new Query())
                    ->select('*')
                    ->from($this->scopeClientTable)
                    ->where('clientId = :clientId', [':clientId' => $clientKey])
                    ->all($this->db);
                foreach ($scopeClients as $scopeClient) {
                    if (($index = array_search($scopeClient['scopeId'], $values['scopes'])) === false) {
                        $this->db->createCommand()
                            ->delete($this->scopeClientTable,
                                'clientId = :clientId AND scopeId = :scopeId',
                                [':clientId' => $clientKey, ':scopeId' => $scopeClient['scopeId']])
                            ->execute();
                    } else {
                        unset($values['scopes'][$index]);
                    }
                }
                foreach ($values['scopes'] as $scope) {
                    $scopeClientParams = [
                        'scopeId' => $scope,
                        'clientId' => $clientKey
                    ];
                    $this->db->createCommand()
                        ->insert($this->scopeClientTable, $scopeClientParams)
                        ->execute();
                }
            }

        } catch (DatabaseException $e) {
            // @codeCoverageIgnoreStart
            // we have a MYSQL exception, we should not discard
            Yii::debug('Error while updating entity', __METHOD__);
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
     * @inheritdoc
     */
    public function findOne($key)
    {
        $record = null;
        $clientData = (new Query())
            ->select('*')
            ->from($this->clientsTable)
            ->where('id = :id', [':id' => $key])
            ->one($this->db);

        if ($clientData !== false) {
            $clientData['grantTypes'] = [];
            $clientData['scopes'] = [];
            $tmpGrantTypes = (new Query())
                ->select('grantTypeId')
                ->from($this->clientGrantTypeTable)
                ->all($this->db);
            foreach ($tmpGrantTypes as $grantType) {
                $clientData['grantTypes'][] = $grantType['grantTypeId'];
            }
            $tmpScopes = (new Query())
                ->select('scopeId')
                ->from($this->scopeClientTable)
                ->all($this->db);
            foreach ($tmpScopes as $scope) {
                $clientData['scopes'][] = $scope['scopeId'];
            }

            $record = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
            /** @var ClientModelInterface $record */
            $properties = $record->attributesDefinition();
            $this->setAttributesDefinitions($properties);
            $attributes = [];
            foreach ($clientData as $key => $value) {
                if (isset($properties[$key]) === true) {
                    $clientData[$key] = $this->convertToModel($key, $value);
                    $record->setAttribute($key, $clientData[$key]);
                    $attributes[$key] = $clientData[$key];
                    // @codeCoverageIgnoreStart
                } elseif ($record->canSetProperty($key)) {
                    // TODO: find a way to test attribute population
                    $record->{$key} = $value;
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
            //TODO: check results to return correct information
            $this->db->createCommand()
                ->delete($this->clientsTable, 'id = :id', [':id' => $client->getKey()])
                ->execute();
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
        $entity = (new Query())
            ->select('*')
            ->from($this->clientUserTable)
            ->where('clientId = :clientId', [':clientId' => $client->getKey()])
            ->andWhere('userId = :userId', [':userId' => $userId])
            ->one($this->db);
        return ($entity !== false);
    }

    /**
     * @inheritdoc
     */
    public function addUser(ClientModelInterface $client, $userId)
    {
        $params = [
            'clientId' => $client->getKey(),
            'userId' => $userId
        ];
        $this->db->createCommand()
            ->insert($this->clientUserTable, $params)
            ->execute();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function removeUser(ClientModelInterface $client, $userId)
    {
        $this->db->createCommand()
            ->delete($this->clientUserTable,
                'clientId = :clientId AND userId = :userId',
                [':clientId' => $client->getKey(), ':userId' => $userId])
            ->execute();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findAllByUserId($userId)
    {
        $clientsList = (new Query())
            ->select('*')
            ->from($this->clientUserTable)
            ->where('userId = :userId', [':userId' => $userId])
            ->all($this->db);
        $clients = [];
        foreach ($clientsList as $client) {
            $result = $this->findOne($client['clientId']);
            if ($result instanceof ClientModelInterface) {
                $clients[] = $result;
            }
        }
        return $clients;
    }
}
