<?php
/**
 * CypherKeyService.php
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
use sweelix\oauth2\server\interfaces\CypherKeyModelInterface;
use sweelix\oauth2\server\interfaces\CypherKeyServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * This is the cypher key service for mySql
 *  database structure
 *    * oauth2:cypherKeys:<aid> : hash (CypherKey)
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\mySql
 * @since 1.0.0
 */
class CypherKeyService extends BaseService implements CypherKeyServiceInterface
{
    /**
     * @var string sql cypherKeys tables
     */
    public $cypherKeysTable = null;

    /**
     * Save Cypher Key
     * @param CypherKeyModelInterface $cypherKey
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     * @since 1.0.0
     */
    protected function insert(CypherKeyModelInterface $cypherKey, $attributes)
    {
        $result = false;
        if (!$cypherKey->beforeSave(true)) {
            return $result;
        }
        $cypherKeyKey = $cypherKey->getKey();
        $entity = (new Query())
            ->select('*')
            ->from($this->cypherKeysTable)
            ->where('id = :id', [':id' => $cypherKeyKey])
            ->one($this->db);
        if ($entity !== false) {
            throw new DuplicateKeyException('Duplicate key "' . $cypherKeyKey . '"');
        }
        $values = $cypherKey->getDirtyAttributes($attributes);
        $cypherKeyParameters = [];
        $this->setAttributesDefinitions($cypherKey->attributesDefinition());
        foreach ($values as $key => $value) {
            if ($value !== null) {
                $cypherKeyParameters[$key] = $this->convertToDatabase($key, $value);
            }
        }
        $cypherKeyParameters['dateCreated'] = new Expression('NOW()');
        $cypherKeyParameters['dateUpdated'] = new Expression('NOW()');
        try {
            $this->db->createCommand()
                ->insert($this->cypherKeysTable, $cypherKeyParameters)
                ->execute();
        } catch (DatabaseException $e) {
            // @codeCoverageIgnoreStart
            // we have a MYSQL exception, we should not discard
            Yii::debug('Error while inserting entity', __METHOD__);
            throw $e;
            // @codeCoverageIgnoreEnd
        }
        $changedAttributes = array_fill_keys(array_keys($values), null);
        $cypherKey->setOldAttributes($values);
        $cypherKey->afterSave(true, $changedAttributes);
        $result = true;
        return $result;
    }

    /**
     * Update Cypher Key
     * @param CypherKeyModelInterface $cypherKey
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     */
    protected function update(CypherKeyModelInterface $cypherKey, $attributes)
    {
        if (!$cypherKey->beforeSave(false)) {
            return false;
        }

        $values = $cypherKey->getDirtyAttributes($attributes);
        $modelKey = $cypherKey->key();
        if (isset($values[$modelKey]) === true) {
            $entity = (new Query())
                ->select('*')
                ->from($this->cypherKeysTable)
                ->where('id = :id', [':id' => $values[$modelKey]])
                ->one($this->db);
            if ($entity !== false) {
                throw new DuplicateKeyException('Duplicate key "' . $values[$modelKey] . '"');
            }
        }
        $cypherKeyKey = isset($values[$modelKey]) ? $values[$modelKey] : $cypherKey->getKey();

        $cypherKeyParameters = [];
        $this->setAttributesDefinitions($cypherKey->attributesDefinition());
        foreach ($values as $key => $value) {
            if ($key !== 'scopes') {
                $cypherKeyParameters[$key] = ($value !== null) ? $this->convertToDatabase($key, $value) : null;
            }
        }
        $cypherKeyParameters['dateUpdated'] = new Expression('NOW()');
        try {
            if (array_key_exists($modelKey, $values) === true) {
                $oldCypherKeyKey = $cypherKey->getOldKey();
                $this->db->createCommand()
                    ->update($this->cypherKeysTable, $cypherKeyParameters, 'id = :id', [':id' => $oldCypherKeyKey])
                    ->execute();
            } else {
                $this->db->createCommand()
                    ->update($this->cypherKeysTable, $cypherKeyParameters, 'id = :id', [':id' => $cypherKeyKey])
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
            $oldAttributes = $cypherKey->getOldAttributes();
            $changedAttributes[$name] = isset($oldAttributes[$name]) ? $oldAttributes[$name] : null;
            $cypherKey->setOldAttribute($name, $value);
        }
        $cypherKey->afterSave(false, $changedAttributes);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function save(CypherKeyModelInterface $cypherKey, $attributes)
    {
        if ($cypherKey->getIsNewRecord()) {
            $result = $this->insert($cypherKey, $attributes);
        } else {
            $result = $this->update($cypherKey, $attributes);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function findOne($key)
    {
        $record = null;
        $CypherKeyData = (new Query())
            ->select('*')
            ->from($this->cypherKeysTable)
            ->where('id = :id', [':id' => $key])
            ->one($this->db);

        if ($CypherKeyData !== false) {
            $record = Yii::createObject('sweelix\oauth2\server\interfaces\CypherKeyModelInterface');
            /** @var CypherKeyModelInterface $record */
            $properties = $record->attributesDefinition();
            $this->setAttributesDefinitions($properties);
            $attributes = [];
            foreach ($CypherKeyData as $key => $value) {
                if (isset($properties[$key]) === true) {
                    $CypherKeyData[$key] = $this->convertToModel($key, $value);
                    $record->setAttribute($key, $CypherKeyData[$key]);
                    $attributes[$key] = $CypherKeyData[$key];
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
    public function delete(CypherKeyModelInterface $cypherKey)
    {
        $result = false;
        if ($cypherKey->beforeDelete()) {
            //TODO: check results to return correct information
            $this->db->createCommand()
                ->delete($this->cypherKeysTable, 'id = :id', [':id' => $cypherKey->getKey()])
                ->execute();
            $cypherKey->setIsNewRecord(true);
            $cypherKey->afterDelete();
            $result = true;
        }
        return $result;
    }
}