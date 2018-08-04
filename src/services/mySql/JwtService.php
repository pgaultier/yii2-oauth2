<?php
/**
 * JwtService.php
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
use sweelix\oauth2\server\interfaces\JwtModelInterface;
use sweelix\oauth2\server\interfaces\JwtServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;
use yii\db\Query;

/**
 * This is the jwt service for mySql
 *  database structure
 *    * oauth2:jwt:<jid> : hash (Jwt)
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\mySql
 * @since 1.0.0
 */
class JwtService extends BaseService implements JwtServiceInterface
{
    /**
     * @var string sql jwts table
     */
    public $jwtsTable = null;

    /**
     * Save Jwt
     * @param JwtModelInterface $jwt
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     * @since 1.0.0
     */
    protected function insert(JwtModelInterface $jwt, $attributes)
    {
        $result = false;
        if (!$jwt->beforeSave(true)) {
            return $result;
        }
        $entity = (new Query())
            ->select('*')
            ->from($this->jwtsTable)
            ->where('id = :id', [':id' => $jwt->id])
            ->one($this->db);
        if ($entity !== false) {
            throw new DuplicateKeyException('Duplicate key "' . $jwt->id . '"');
        }
        $values = $jwt->getDirtyAttributes($attributes);
        $jwtParameters = [];
        $this->setAttributesDefinitions($jwt->attributesDefinition());
        foreach ($values as $key => $value) {
            if ($value !== null) {
                $jwtParameters[$key] = $this->convertToDatabase($key, $value);
            }
        }
        $jwtParameters['dateCreated'] = date('Y-m-d H:i:s');
        $jwtParameters['dateUpdated'] = date('Y-m-d H:i:s');
        try {
            $this->db->createCommand()
                ->insert($this->jwtsTable, $jwtParameters)
                ->execute();
        } catch (DatabaseException $e) {
            // @codeCoverageIgnoreStart
            // we have a MYSQL exception, we should not discard
            Yii::debug('Error while inserting entity', __METHOD__);
            throw $e;
            // @codeCoverageIgnoreEnd
        }
        $changedAttributes = array_fill_keys(array_keys($values), null);
        $jwt->setOldAttributes($values);
        $jwt->afterSave(true, $changedAttributes);
        $result = true;
        return $result;
    }

    /**
     * Update Jwt
     * @param JwtModelInterface $jwt
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     */
    protected function update(JwtModelInterface $jwt, $attributes)
    {
        if (!$jwt->beforeSave(false)) {
            return false;
        }

        $values = $jwt->getDirtyAttributes($attributes);
        $modelKey = $jwt->key();
        if (isset($values[$modelKey]) === true) {
            $entity = (new Query())
                ->select('*')
                ->from($this->jwtsTable)
                ->where('id = :id', [':id' => $values[$modelKey]])
                ->one($this->db);
            if ($entity !== false) {
                throw new DuplicateKeyException('Duplicate key "' . $values[$modelKey] . '"');
            }
        }
        $jwtKey = isset($values[$modelKey]) ? $values[$modelKey] : $jwt->getKey();

        $jwtParameters = [];
        $this->setAttributesDefinitions($jwt->attributesDefinition());
        foreach ($values as $key => $value) {
            $jwtParameters[$key] = ($value !== null) ? $this->convertToDatabase($key, $value) : null;
        }
        $jwtParameters['dateUpdated'] = date('Y-m-d H:i:s');
        try {
            if (array_key_exists($modelKey, $values) === true) {
                $oldJwtKey = $jwt->getOldKey();
                $this->db->createCommand()
                    ->update($this->jwtsTable, $jwtParameters, 'id = :id', [':id' => $oldJwtKey])
                    ->execute();
            } else {
                $this->db->createCommand()
                    ->update($this->jwtsTable, $jwtParameters, 'id = :id', [':id' => $jwtKey])
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
            $oldAttributes = $jwt->getOldAttributes();
            $changedAttributes[$name] = isset($oldAttributes[$name]) ? $oldAttributes[$name] : null;
            $jwt->setOldAttribute($name, $value);
        }
        $jwt->afterSave(false, $changedAttributes);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function save(JwtModelInterface $jwt, $attributes)
    {
        if ($jwt->getIsNewRecord()) {
            $result = $this->insert($jwt, $attributes);
        } else {
            $result = $this->update($jwt, $attributes);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function findOne($key)
    {
        $record = null;
        $jwtData = (new Query())
            ->select('*')
            ->from($this->jwtsTable)
            ->where('id = :id', [':id' => $key])
            ->one($this->db);
        if ($jwtData !== false) {
            $record = Yii::createObject('sweelix\oauth2\server\interfaces\JwtModelInterface');
            /** @var JwtModelInterface $record */
            $properties = $record->attributesDefinition();
            $this->setAttributesDefinitions($properties);
            $attributes = [];
            foreach ($jwtData as $key => $value) {
                if (isset($properties[$key]) === true) {
                    $jwtData[$key] = $this->convertToModel($key, $value);
                    $record->setAttribute($key, $jwtData[$key]);
                    $attributes[$key] = $jwtData[$key];
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
    public function delete(JwtModelInterface $jwt)
    {
        $result = false;
        if ($jwt->beforeDelete()) {
            //TODO: check results to return correct information
            $this->db->createCommand()
                ->delete($this->jwtsTable, 'id = :id', [':id' => $jwt->getKey()])
                ->execute();
            $jwt->setIsNewRecord(true);
            $jwt->afterDelete();
            $result = true;
        }
        return $result;
    }
}