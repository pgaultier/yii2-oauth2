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
 * @package sweelix\oauth2\server\services\mySql
 */

namespace sweelix\oauth2\server\services\mySql;

use sweelix\oauth2\server\exceptions\DuplicateIndexException;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
use sweelix\oauth2\server\interfaces\ScopeModelInterface;
use sweelix\oauth2\server\interfaces\ScopeServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;
use yii\db\Query;

/**
 * This is the scope service for mySql
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
 * @package sweelix\oauth2\server\services\mySql
 * @since 1.0.0
 */
class ScopeService extends BaseService implements ScopeServiceInterface
{
    /**
     * @var string sql scope client table
     */
    public $scopeClientTable = null;

    /**
     * @var string sql scopes table
     */
    public $scopesTable = null;

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
        $entity = (new Query())
            ->select('*')
            ->from($this->scopesTable)
            ->where('id = :id', [':id' => $scope->id])
            ->one($this->db);
        if ($entity !== false) {
            throw new DuplicateKeyException('Duplicate key "' . $scope->id . '"');
        }
        $values = $scope->getDirtyAttributes($attributes);
        $scopeParameters = [];
        $this->setAttributesDefinitions($scope->attributesDefinition());
        foreach ($values as $key => $value) {
            if ($value !== null) {
                $scopeParameters[$key] = $this->convertToDatabase($key, $value);
            }
        }
        $scopeParameters['dateCreated'] = date('Y-m-d H:i:s');
        $scopeParameters['dateUpdated'] = date('Y-m-d H:i:s');
        try {
            $this->db->createCommand()
                ->insert($this->scopesTable, $scopeParameters)
                ->execute();
        } catch (DatabaseException $e) {
            // @codeCoverageIgnoreStart
            // we have a MYSQL exception, we should not discard
            Yii::debug('Error while inserting entity', __METHOD__);
            throw $e;
            // @codeCoverageIgnoreEnd
        }
        $changedAttributes = array_fill_keys(array_keys($values), null);
        $scope->setOldAttributes($values);
        $scope->afterSave(true, $changedAttributes);
        $result = true;
        return $result;
    }

    /**
     * Update ScopeModelInterface
     * @param ScopeModelInterface $scope
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
        if (isset($values[$modelKey]) === true) {
            $entity = (new Query())
                ->select('*')
                ->from($this->scopesTable)
                ->where('id = :id', [':id' => $values[$modelKey]])
                ->one($this->db);
            if ($entity !== false) {
                throw new DuplicateKeyException('Duplicate key "' . $values[$modelKey] . '"');
            }
        }
        $scopeKey = isset($values[$modelKey]) ? $values[$modelKey] : $scope->getKey();

        $scopeParameters = [];
        $this->setAttributesDefinitions($scope->attributesDefinition());
        foreach ($values as $key => $value) {
            $scopeParameters[$key] = ($value !== null) ? $this->convertToDatabase($key, $value) : null;
        }
        $scopeParameters['dateUpdated'] = date('Y-m-d H:i:s');
        try {
            if (array_key_exists($modelKey, $values) === true) {
                $oldScopeKey = $scope->getOldKey();
                $this->db->createCommand()
                    ->update($this->scopesTable, $scopeParameters, 'id = :id', [':id' => $oldScopeKey])
                    ->execute();
            } else {
                $this->db->createCommand()
                    ->update($this->scopesTable, $scopeParameters, 'id = :id', [':id' => $scopeKey])
                    ->execute();
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
     * @inheritdoc
     */
    public function findOne($key)
    {
        $record = null;
        $scopeData = (new Query())
            ->select('*')
            ->from($this->scopesTable)
            ->where('id = :id', [':id' => $key])
            ->one($this->db);
        if ($scopeData !== false) {
            $record = Yii::createObject('sweelix\oauth2\server\interfaces\ScopeModelInterface');
            /** @var ScopeModelInterface $record */
            $properties = $record->attributesDefinition();
            $this->setAttributesDefinitions($properties);
            $attributes = [];
            foreach ($scopeData as $key => $value) {
                if (isset($properties[$key]) === true) {
                    $scopeData[$key] = $this->convertToModel($key, $value);
                    $record->setAttribute($key, $scopeData[$key]);
                    $attributes[$key] = $scopeData[$key];
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
    public function delete(ScopeModelInterface $scope)
    {
        $result = false;
        if ($scope->beforeDelete()) {
            //TODO: check results to return correct information
            $this->db->createCommand()
                ->delete($this->scopesTable, 'id = :id', [':id' => $scope->getKey()])
                ->execute();
            $scope->setIsNewRecord(true);
            $scope->afterDelete();
            $result = true;
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function findAvailableScopeIds()
    {
        $tmpScopes = (new Query())
            ->select('id')
            ->from($this->scopesTable)
            ->all($this->db);
        $scopes = [];
        foreach ($tmpScopes as $scope) {
            $scopes[] = $scope['id'];
        }
        return $scopes;
    }

    /**
     * @inheritdoc
     */
    public function findDefaultScopeIds($clientId = null)
    {
        //TODO: add default scopes for clients
        $defaultScopeIds = [];
        if ($clientId !== null) {
            $tmpScopeIds = (new Query())
                ->select('scopeId as ID')
                ->from($this->scopeClientTable)
                ->where('clientId = :clientId', [':clientId' => $clientId])
                ->all($this->db);
        } else {
            $tmpScopeIds = (new Query())
                ->select('id as ID')
                ->from($this->scopesTable)
                ->where('isDefault = :isDefault', [':isDefault' => true])
                ->all($this->db);
        }
        foreach ($tmpScopeIds as $tmpScopeId) {
            $defaultScopeIds[] = $tmpScopeId['ID'];
        }
        return $defaultScopeIds;
    }
}
