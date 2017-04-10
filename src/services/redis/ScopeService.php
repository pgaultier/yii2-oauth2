<?php
/**
 * ScopeService.php
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
use sweelix\oauth2\server\interfaces\ScopeModelInterface;
use sweelix\oauth2\server\models\Scope;
use sweelix\oauth2\server\interfaces\ScopeServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;

/**
 * This is the scope service for redis
 *  database structure
 *    * oauth2:scopes:<sid> : hash (Scope)
 *    * oauth2:scopes:keys : set scopeIds
 *    * oauth2:scopes:defaultkeys : set default scopeIds
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\redis
 * @since 1.0.0
 */
class ScopeService extends BaseService implements ScopeServiceInterface
{

    /**
     * @param string $sid scope ID
     * @return string scope Key
     * @since 1.0.0
     */
    protected function getScopeKey($sid)
    {
        return $this->namespace . ':' . $sid;
    }

    /**
     * @return string key of all scopes list
     * @since 1.0.0
     */
    protected function getScopeListKey()
    {
        return $this->namespace . ':keys';
    }

    /**
     * @return string key of default scopes list
     * @since 1.0.0
     */
    protected function getScopeDefaultListKey()
    {
        return $this->namespace . ':defaultkeys';
    }

    /**
     * @inheritdoc
     */
    public function save(ScopeModelInterface $scope, $attributes)
    {
        if ($scope->getIsNewRecord()) {
            $result = $this->insert($scope, $attributes);
        } else {
            $result = $this->update($scope, $attributes);
        }
        return $result;
    }

    /**
     * Save Scope
     * @param ScopeModelInterface $scope
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     * @since 1.0.0
     */
    protected function insert(ScopeModelInterface $scope, $attributes)
    {
        $result = false;
        if (!$scope->beforeSave(true)) {
            return $result;
        }
        $scopeKey = $this->getScopeKey($scope->getKey());
        $scopeListKey = $this->getScopeListKey();
        $scopeDefaultListKey = $this->getScopeDefaultListKey();
        //check if record exists
        $entityStatus = (int)$this->db->executeCommand('EXISTS', [$scopeKey]);
        if ($entityStatus === 1) {
            throw new DuplicateKeyException('Duplicate key "'.$scopeKey.'"');
        }

        $values = $scope->getDirtyAttributes($attributes);
        $redisParameters = [$scopeKey];
        $this->setAttributesDefinitions($scope->attributesDefinition());
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
                $this->db->executeCommand('SADD', [$scopeListKey, $scope->getKey()]);
                if ($scope->isDefault === true) {
                    $this->db->executeCommand('SADD', [$scopeDefaultListKey, $scope->getKey()]);
                }
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
        $scope->setOldAttributes($values);
        $scope->afterSave(true, $changedAttributes);
        $result = true;
        return $result;
    }


    /**
     * Update ScopeModelInterface
     * @param Scope $scope
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     */
    protected function update(ScopeModelInterface $scope, $attributes)
    {
        if (!$scope->beforeSave(false)) {
            return false;
        }

        $values = $scope->getDirtyAttributes($attributes);
        $modelKey = $scope->key();
        $scopeId = isset($values[$modelKey]) ? $values[$modelKey] : $scope->getKey();
        $scopeKey = $this->getScopeKey($scopeId);
        $scopeListKey = $this->getScopeListKey();
        $scopeDefaultListKey = $this->getScopeDefaultListKey();


        if (isset($values[$modelKey]) === true) {
            $newScopeKey = $this->getScopeKey($values[$modelKey]);
            $entityStatus = (int)$this->db->executeCommand('EXISTS', [$newScopeKey]);
            if ($entityStatus === 1) {
                throw new DuplicateKeyException('Duplicate key "'.$newScopeKey.'"');
            }
        }

        $this->db->executeCommand('MULTI');
        try {
            $reAddKeyInList = false;
            if (array_key_exists($modelKey, $values) === true) {
                $oldId = $scope->getOldKey();
                $oldScopeKey = $this->getScopeKey($oldId);

                $this->db->executeCommand('RENAMENX', [$oldScopeKey, $scopeKey]);
                $this->db->executeCommand('SREM', [$scopeListKey, $oldScopeKey]);
                $this->db->executeCommand('SREM', [$scopeDefaultListKey, $oldScopeKey]);
                $reAddKeyInList = true;
            }

            $redisUpdateParameters = [$scopeKey];
            $redisDeleteParameters = [$scopeKey];
            $this->setAttributesDefinitions($scope->attributesDefinition());
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

            if ($reAddKeyInList === true) {
                $this->db->executeCommand('SADD', [$scopeListKey, $scopeId]);
            }
            if ($scope->isDefault === true) {
                $this->db->executeCommand('SADD', [$scopeDefaultListKey, $scopeId]);
            } else {
                $this->db->executeCommand('SREM', [$scopeDefaultListKey, $scopeId]);
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
            $oldAttributes = $scope->getOldAttributes();
            $changedAttributes[$name] = isset($oldAttributes[$name]) ? $oldAttributes[$name] : null;
            $scope->setOldAttribute($name, $value);
        }
        $scope->afterSave(false, $changedAttributes);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findOne($key)
    {
        $record = null;
        $scopeKey = $this->getScopeKey($key);
        $scopeExists = (bool)$this->db->executeCommand('EXISTS', [$scopeKey]);
        if ($scopeExists === true) {
            $scopeData = $this->db->executeCommand('HGETALL', [$scopeKey]);
            $record = Yii::createObject('sweelix\oauth2\server\interfaces\ScopeModelInterface');
            /** @var ScopeModelInterface $record */
            $properties = $record->attributesDefinition();
            $this->setAttributesDefinitions($properties);
            $attributes = [];
            for ($i = 0; $i < count($scopeData); $i += 2) {
                if (isset($properties[$scopeData[$i]]) === true) {
                    $scopeData[$i + 1] = $this->convertToModel($scopeData[$i], $scopeData[($i + 1)]);
                    $record->setAttribute($scopeData[$i], $scopeData[$i + 1]);
                    $attributes[$scopeData[$i]] = $scopeData[$i + 1];
                // @codeCoverageIgnoreStart
                } elseif ($record->canSetProperty($scopeData[$i])) {
                    // TODO: find a way to test attribute population
                    $record->{$scopeData[$i]} = $scopeData[$i + 1];
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
    public function findAvailableScopeIds()
    {
        $scopeListKey = $this->getScopeListKey();
        return $this->db->executeCommand('SMEMBERS', [$scopeListKey]);
    }

    /**
     * @inheritdoc
     */
    public function findDefaultScopeIds($clientId = null)
    {
        //TODO: add default scopes for clients
        $scopeDefaultListKey = $this->getScopeDefaultListKey();
        return $this->db->executeCommand('SMEMBERS', [$scopeDefaultListKey]);
    }

    /**
     * @inheritdoc
     */
    public function delete(ScopeModelInterface $scope)
    {
        $result = false;
        if ($scope->beforeDelete()) {
            $this->db->executeCommand('MULTI');
            $id = $scope->getOldKey();
            $scopeKey = $this->getScopeKey($id);
            $scopeListKey = $this->getScopeListKey();
            $scopeDefaultListKey = $this->getScopeDefaultListKey();


            $this->db->executeCommand('DEL', [$scopeKey]);
            $this->db->executeCommand('SREM', [$scopeListKey, $id]);
            $this->db->executeCommand('SREM', [$scopeDefaultListKey, $id]);
            //TODO: check results to return correct information
            $queryResult = $this->db->executeCommand('EXEC');
            $scope->setIsNewRecord(true);
            $scope->afterDelete();
            $result = true;
        }
        return $result;
    }

}
