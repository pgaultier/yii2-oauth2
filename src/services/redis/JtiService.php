<?php
/**
 * JtiService.php
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
use sweelix\oauth2\server\interfaces\JtiModelInterface;
use sweelix\oauth2\server\interfaces\JtiServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;
use Exception;

/**
 * This is the jti service for redis
 *  database structure
 *    * oauth2:jti:<jid> : hash (Jti)
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\redis
 * @since 1.0.0
 */
class JtiService extends BaseService implements JtiServiceInterface
{

    /**
     * @param string $jid jti ID
     * @return string access token Key
     * @since 1.0.0
     */
    protected function getJtiKey($jid)
    {
        return $this->namespace . ':' . $jid;
    }

    /**
     * @inheritdoc
     */
    public function save(JtiModelInterface $jti, $attributes)
    {
        if ($jti->getIsNewRecord()) {
            $result = $this->insert($jti, $attributes);
        } else {
            $result = $this->update($jti, $attributes);
        }
        return $result;
    }

    /**
     * Save Jti
     * @param JtiModelInterface $jti
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     * @since 1.0.0
     */
    protected function insert(JtiModelInterface $jti, $attributes)
    {
        $result = false;
        if (!$jti->beforeSave(true)) {
            return $result;
        }
        $jtiKey = $this->getJtiKey($jti->getKey());
        //check if record exists
        $entityStatus = (int)$this->db->executeCommand('EXISTS', [$jtiKey]);
        if ($entityStatus === 1) {
            throw new DuplicateKeyException('Duplicate key "'.$jtiKey.'"');
        }

        $values = $jti->getDirtyAttributes($attributes);
        $redisParameters = [$jtiKey];
        $this->setAttributesDefinitions($jti->attributesDefinition());
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
                Yii::debug('Error while inserting entity', __METHOD__);
                throw $e;
                // @codeCoverageIgnoreEnd
            }
        }
        $changedAttributes = array_fill_keys(array_keys($values), null);
        $jti->setOldAttributes($values);
        $jti->afterSave(true, $changedAttributes);
        $result = true;
        return $result;
    }


    /**
     * Update Jti
     * @param JtiModelInterface $jti
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     */
    protected function update(JtiModelInterface $jti, $attributes)
    {
        if (!$jti->beforeSave(false)) {
            return false;
        }

        $values = $jti->getDirtyAttributes($attributes);
        $modelKey = $jti->key();
        $jtiId = isset($values[$modelKey]) ? $values[$modelKey] : $jti->getKey();
        $jtiKey = $this->getJtiKey($jtiId);


        if (isset($values[$modelKey]) === true) {
            $newJtiKey = $this->getJtiKey($values[$modelKey]);
            $entityStatus = (int)$this->db->executeCommand('EXISTS', [$newJtiKey]);
            if ($entityStatus === 1) {
                throw new DuplicateKeyException('Duplicate key "'.$newJtiKey.'"');
            }
        }

        $this->db->executeCommand('MULTI');
        try {
            if (array_key_exists($modelKey, $values) === true) {
                $oldId = $jti->getOldKey();
                $oldJtiKey = $this->getJtiKey($oldId);

                $this->db->executeCommand('RENAMENX', [$oldJtiKey, $jtiKey]);
            }

            $redisUpdateParameters = [$jtiKey];
            $redisDeleteParameters = [$jtiKey];
            $this->setAttributesDefinitions($jti->attributesDefinition());
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
            Yii::debug('Error while updating entity', __METHOD__);
            throw $e;
            // @codeCoverageIgnoreEnd
        }

        $changedAttributes = [];
        foreach ($values as $name => $value) {
            $oldAttributes = $jti->getOldAttributes();
            $changedAttributes[$name] = isset($oldAttributes[$name]) ? $oldAttributes[$name] : null;
            $jti->setOldAttribute($name, $value);
        }
        $jti->afterSave(false, $changedAttributes);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findOne($key)
    {
        $record = null;
        $jtiKey = $this->getJtiKey($key);
        $jtiExists = (bool)$this->db->executeCommand('EXISTS', [$jtiKey]);
        if ($jtiExists === true) {
            $jtiData = $this->db->executeCommand('HGETALL', [$jtiKey]);
            $record = Yii::createObject('sweelix\oauth2\server\interfaces\JtiModelInterface');
            /** @var JtiModelInterface $record */
            $properties = $record->attributesDefinition();
            $this->setAttributesDefinitions($properties);
            $attributes = [];
            for ($i = 0; $i < count($jtiData); $i += 2) {
                if (isset($properties[$jtiData[$i]]) === true) {
                    $jtiData[$i + 1] = $this->convertToModel($jtiData[$i], $jtiData[($i + 1)]);
                    $record->setAttribute($jtiData[$i], $jtiData[$i + 1]);
                    $attributes[$jtiData[$i]] = $jtiData[$i + 1];
                    // @codeCoverageIgnoreStart
                } elseif ($record->canSetProperty($jtiData[$i])) {
                    // TODO: find a way to test attribute population
                    $record->{$jtiData[$i]} = $jtiData[$i + 1];
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
    public function delete(JtiModelInterface $jti)
    {
        $result = false;
        if ($jti->beforeDelete()) {
            $this->db->executeCommand('MULTI');
            $id = $jti->getOldKey();
            $jtiKey = $this->getJtiKey($id);

            $this->db->executeCommand('DEL', [$jtiKey]);
            //TODO: check results to return correct information
            $queryResult = $this->db->executeCommand('EXEC');
            $jti->setIsNewRecord(true);
            $jti->afterDelete();
            $result = true;
        }
        return $result;
    }

}
