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
     * @var string subject namespace (collection for jtis)
     */
    public $subjectNamespace = '';

    /**
     * @var string client namespace (collection for jtis)
     */
    public $clientNamespace = '';

    /**
     * @param string $jid jti ID
     * @return string access token Key
     * @since 1.0.0
     */
    public function getJtiKey($jid)
    {
        return $this->namespace . ':' . $jid;
    }

    /**
     * @param string $sid subject ID
     * @return string user jtis collection Key
     */
    public function getSubjectJtisKey($sid)
    {
        return $this->subjectNamespace . ':' . $sid . ':jtis';
    }

    /**
     * @param string $cid client ID
     * @return string client jtis collection Key
     */
    public function getClientJtisKey($cid)
    {
        return $this->clientNamespace . ':' . $cid . ':jtis';
    }

    /**
     * @return string key of all jtis list
     */
    public function getJtiListKey()
    {
        return $this->namespace . ':keys';
    }

    /**
     * @return string key of all subjects list
     */
    public function getSubjectListKey()
    {
        return $this->subjectNamespace . ':keys';
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
        $jtiId = $jti->getKey();
        $jtiKey = $this->getJtiKey($jtiId);
        if (empty($jti->subject) === false) {
            $subjectJtisKey = $this->getSubjectJtisKey($jti->subject);
        } else {
            $subjectJtisKey = null;
        }
        $clientJtisKey = $this->getClientJtisKey($jti->clientId);
        $jtiListKey = $this->getJtiListKey();
        $subjectListKey = $this->getSubjectListKey();

        //check if record exists
        $entityStatus = (int)$this->db->executeCommand('EXISTS', [$jtiKey]);
        if ($entityStatus === 1) {
            throw new DuplicateKeyException('Duplicate key "' . $jtiKey . '"');
        }

        $values = $jti->getDirtyAttributes($attributes);
        $redisParameters = [$jtiKey];
        $this->setAttributesDefinitions($jti->attributesDefinition());
        $expire = null;
        foreach ($values as $key => $value) {
            if (($key === 'expires') && ($value > 0)) {
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
                    $this->db->executeCommand('EXPIREAT', [$jtiKey, $expire]);
                }
                if ($subjectJtisKey !== null) {
                    $this->db->executeCommand('ZADD', [$subjectJtisKey, $expire === false ? -1 : $expire, $jtiId]);
                }
                $this->db->executeCommand('ZADD', [$clientJtisKey, $expire === false ? -1 : $expire, $jtiId]);
                $this->db->executeCommand('ZADD', [$jtiListKey, $expire === false ? -1 : $expire, $jtiId]);
                $this->db->executeCommand('SADD', [$subjectListKey, $jti->subject]);
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
        $jtiListKey = $this->getJtiListKey();
        $subjectListKey = $this->getSubjectListKey();

        if (empty($jti->subject) === false) {
            $subjectJtisKey = $this->getSubjectJtisKey($jti->subject);
        } else {
            $subjectJtisKey = null;
        }
        $clientJtisKey = $this->getClientJtisKey($jti->clientId);

        if (isset($values[$modelKey]) === true) {
            $newJtiKey = $this->getJtiKey($values[$modelKey]);
            $entityStatus = (int)$this->db->executeCommand('EXISTS', [$newJtiKey]);
            if ($entityStatus === 1) {
                throw new DuplicateKeyException('Duplicate key "' . $newJtiKey . '"');
            }
        }

        $this->db->executeCommand('MULTI');
        try {
            $reAddKeyInList = false;
            if (array_key_exists($modelKey, $values) === true) {
                $oldId = $jti->getOldKey();
                $oldJtiKey = $this->getJtiKey($oldId);

                $this->db->executeCommand('RENAMENX', [$oldJtiKey, $jtiKey]);
                if ($subjectJtisKey !== null) {
                    $this->db->executeCommand('ZREM', [$subjectJtisKey, $oldJtiKey]);
                }
                $this->db->executeCommand('ZREM', [$clientJtisKey, $oldJtiKey]);
                $this->db->executeCommand('ZREM', [$jtiListKey, $oldJtiKey]);
                $reAddKeyInList = true;
            }

            $redisUpdateParameters = [$jtiKey];
            $redisDeleteParameters = [$jtiKey];
            $this->setAttributesDefinitions($jti->attributesDefinition());
            $expire = $jti->expires;
            foreach ($values as $key => $value) {
                if ($value === null) {
                    if ($key === 'expires') {
                        $expire = false;
                    }
                    $redisDeleteParameters[] = $key;
                } else {
                    if (($key === 'expires') && ($value > 0)) {
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
                $this->db->executeCommand('PERSIST', [$jtiKey]);
            } elseif ($expire > 0) {
                $this->db->executeCommand('EXPIREAT', [$jtiKey, $expire]);
            }

            if ($reAddKeyInList === true) {
                if ($subjectJtisKey !== null) {
                    $this->db->executeCommand('ZADD', [$subjectJtisKey, $expire === false ? -1 : $expire, $jtiKey]);
                }
                $this->db->executeCommand('ZADD', [$clientJtisKey, $expire === false ? -1 : $expire, $jtiKey]);
                $this->db->executeCommand('ZADD', [$jtiListKey, $expire === false ? -1 : $expire, $jtiKey]);
            }
            $this->db->executeCommand('SADD', [$subjectListKey, $jti->subject]);

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
    public function findAllBySubject($subject)
    {
        $subjectJtisKey = $this->getSubjectJtisKey($subject);
        $subjectJtis = $this->db->executeCommand('ZRANGE', [$subjectJtisKey, 0, -1]);
        $jtis = [];
        if ((is_array($subjectJtis) === true) && (count($subjectJtis) > 0)) {
            foreach ($subjectJtis as $subjectJtiId) {
                $jtis[] = $this->findOne($subjectJtiId);
            }
        }
        return $jtis;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllBySubject($subject)
    {
        $subjectJtisKey = $this->getSubjectJtisKey($subject);
        $subjectJtis = $this->db->executeCommand('ZRANGE', [$subjectJtisKey, 0, -1]);
        foreach ($subjectJtis as $subjectJtiId) {
            $subjectJti = $this->findOne($subjectJtiId);
            if ($subjectJti instanceof JtiModelInterface) {
                $this->delete($subjectJti);
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findAllByClientId($clientId)
    {
        $clientJtisKey = $this->getClientJtisKey($clientId);
        $clientJtis = $this->db->executeCommand('ZRANGE', [$clientJtisKey, 0, -1]);
        $jtis = [];
        if ((is_array($clientJtis) === true) && (count($clientJtis) > 0)) {
            foreach ($clientJtis as $clientJtiId) {
                $jtis[] = $this->findOne($clientJtiId);
            }
        }
        return $jtis;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllByClientId($clientId)
    {
        $clientJtisKey = $this->getClientJtisKey($clientId);
        $clientJtis = $this->db->executeCommand('ZRANGE', [$clientJtisKey, 0, -1]);
        foreach ($clientJtis as $clientJtiId) {
            $clientJti = $this->findOne($clientJtiId);
            if ($clientJti instanceof JtiModelInterface) {
                $this->delete($clientJti);
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete(JtiModelInterface $jti)
    {
        $result = false;
        if ($jti->beforeDelete()) {
            if (empty($jti->subject) === false) {
                $subjectJtisKey = $this->getSubjectJtisKey($jti->subject);
            } else {
                $subjectJtisKey = null;
            }
            $clientJtisKey = $this->getClientJtisKey($jti->clientId);
            $jtiListKey = $this->getJtiListKey();

            $this->db->executeCommand('MULTI');
            $id = $jti->getOldKey();
            $jtiKey = $this->getJtiKey($id);

            $this->db->executeCommand('DEL', [$jtiKey]);
            if ($subjectJtisKey !== null) {
                $this->db->executeCommand('ZREM', [$subjectJtisKey, $id]);
            }
            $this->db->executeCommand('ZREM', [$clientJtisKey, $id]);
            $this->db->executeCommand('ZREM', [$jtiListKey, $id]);
            //TODO: check results to return correct information
            $this->db->executeCommand('EXEC');
            $jti->setIsNewRecord(true);
            $jti->afterDelete();
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
        $jtiListKey = $this->getJtiListKey();
        $this->db->executeCommand('ZREMRANGEBYSCORE', [$jtiListKey, -1, $date]);

        $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        $clientClass = get_class($client);
        /* @var \sweelix\oauth2\server\interfaces\ClientModelInterface[] $clientList */
        $clientList = $clientClass::findAll();
        foreach ($clientList as $client) {
            $clientJtisKey = $this->getClientJtisKey($client->getKey());
            $this->db->executeCommand('ZREMRANGEBYSCORE', [$clientJtisKey, -1, $date]);
        }

        $subjectListKey = $this->getSubjectListKey();
        $subjects = $this->db->executeCommand('SMEMBERS', [$subjectListKey]);
        foreach ($subjects as $subject) {
            $subjectJtisKey = $this->getSubjectJtisKey($subject);
            $this->db->executeCommand('ZREMRANGEBYSCORE', [$subjectJtisKey, '-inf', $date]);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        $jtiListKey = $this->getJtiListKey();
        $jtiList = $this->db->executeCommand('ZRANGE', [$jtiListKey, 0, -1]);
        $jtis = [];
        if ((is_array($jtiList) === true) && (count($jtiList) > 0)) {
            foreach ($jtiList as $jtiId) {
                $jtis[] = $this->findOne($jtiId);
            }
        }
        return $jtis;
    }
}
