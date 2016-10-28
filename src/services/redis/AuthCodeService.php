<?php
/**
 * AuthCodeService.php
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
use sweelix\oauth2\server\interfaces\AuthCodeModelInterface;
use sweelix\oauth2\server\interfaces\AuthCodeServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;

/**
 * This is the auth code service for redis
 *  database structure
 *    * oauth2:authCodes:<aid> : hash (AuthCode)
 *    * oauth2:authCodes:etags : hash <aid> -> <etag>
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\redis
 * @since XXX
 */
class AuthCodeService extends BaseService implements AuthCodeServiceInterface
{

    /**
     * @param string $aid auth code ID
     * @return string auth code Key
     * @since XXX
     */
    protected function getAuthCodeKey($aid)
    {
        return $this->namespace . ':' . $aid;
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
    public function save(AuthCodeModelInterface $authCode, $attributes)
    {
        if ($authCode->getIsNewRecord()) {
            $result = $this->insert($authCode, $attributes);
        } else {
            $result = $this->update($authCode, $attributes);
        }
        return $result;
    }

    /**
     * Save Auth Code
     * @param AuthCodeModelInterface $authCode
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     * @since XXX
     */
    protected function insert(AuthCodeModelInterface $authCode, $attributes)
    {
        $result = false;
        if (!$authCode->beforeSave(true)) {
            return $result;
        }
        $authCodeKey = $this->getAuthCodeKey($authCode->id);
        $etagKey = $this->getEtagIndexKey();
        //check if record exists
        $entityStatus = (int)$this->db->executeCommand('EXISTS', [$authCodeKey]);
        if ($entityStatus === 1) {
            throw new DuplicateKeyException('Duplicate key "'.$authCodeKey.'"');
        }

        $values = $authCode->getDirtyAttributes($attributes);
        $redisParameters = [$authCodeKey];
        $this->setAttributesDefinitions($authCode->attributesDefinition());
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
                $etag = $this->computeEtag($authCode);
                $this->db->executeCommand('HSET', [$etagKey, $authCode->id, $etag]);
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
        $authCode->setOldAttributes($values);
        $authCode->afterSave(true, $changedAttributes);
        $result = true;
        return $result;
    }


    /**
     * Update Auth Code
     * @param AuthCodeModelInterface $authCode
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     */
    protected function update(AuthCodeModelInterface $authCode, $attributes)
    {
        if (!$authCode->beforeSave(false)) {
            return false;
        }

        $etagKey = $this->getEtagIndexKey();
        $values = $authCode->getDirtyAttributes($attributes);
        $authCodeId = isset($values['id']) ? $values['id'] : $authCode->id;
        $authCodeKey = $this->getAuthCodeKey($authCodeId);


        if (isset($values['id']) === true) {
            $newAuthCodeKey = $this->getAuthCodeKey($values['id']);
            $entityStatus = (int)$this->db->executeCommand('EXISTS', [$newAuthCodeKey]);
            if ($entityStatus === 1) {
                throw new DuplicateKeyException('Duplicate key "'.$newAuthCodeKey.'"');
            }
        }

        $this->db->executeCommand('MULTI');
        try {
            if (array_key_exists('id', $values) === true) {
                $oldId = $authCode->getOldAttribute('id');
                $oldAuthCodeKey = $this->getAuthCodeKey($oldId);

                $this->db->executeCommand('RENAMENX', [$oldAuthCodeKey, $authCodeKey]);
                $this->db->executeCommand('HDEL', [$etagKey, $oldAuthCodeKey]);
            }

            $redisUpdateParameters = [$authCodeKey];
            $redisDeleteParameters = [$authCodeKey];
            $this->setAttributesDefinitions($authCode->attributesDefinition());
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

            $etag = $this->computeEtag($authCode);
            $this->db->executeCommand('HSET', [$etagKey, $authCodeId, $etag]);

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
            $oldAttributes = $authCode->getOldAttributes();
            $changedAttributes[$name] = isset($oldAttributes[$name]) ? $oldAttributes[$name] : null;
            $authCode->setOldAttribute($name, $value);
        }
        $authCode->afterSave(false, $changedAttributes);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findOne($key)
    {
        $record = null;
        $authCodeKey = $this->getAuthCodeKey($key);
        $authCodeExists = (bool)$this->db->executeCommand('EXISTS', [$authCodeKey]);
        if ($authCodeExists === true) {
            $authCodeData = $this->db->executeCommand('HGETALL', [$authCodeKey]);
            $record = Yii::createObject('sweelix\oauth2\server\interfaces\AuthCodeModelInterface');
            /** @var AuthCodeModelInterface $record */
            $properties = $record->attributesDefinition();
            $this->setAttributesDefinitions($properties);
            $attributes = [];
            for ($i = 0; $i < count($authCodeData); $i += 2) {
                if (isset($properties[$authCodeData[$i]]) === true) {
                    $authCodeData[$i + 1] = $this->convertToModel($authCodeData[$i], $authCodeData[($i + 1)]);
                    $record->setAttribute($authCodeData[$i], $authCodeData[$i + 1]);
                    $attributes[$authCodeData[$i]] = $authCodeData[$i + 1];
                // @codeCoverageIgnoreStart
                } elseif ($record->canSetProperty($authCodeData[$i])) {
                    // TODO: find a way to test attribute population
                    $record->{$authCodeData[$i]} = $authCodeData[$i + 1];
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
    public function delete(AuthCodeModelInterface $authCode)
    {
        $result = false;
        if ($authCode->beforeDelete()) {
            $etagKey = $this->getEtagIndexKey();
            $this->db->executeCommand('MULTI');
            $id = $authCode->getOldKey();
            $authCodeKey = $this->getAuthCodeKey($id);

            $this->db->executeCommand('HDEL', [$etagKey, $id]);
            $this->db->executeCommand('DEL', [$authCodeKey]);
            //TODO: check results to return correct information
            $queryResult = $this->db->executeCommand('EXEC');
            $authCode->setIsNewRecord(true);
            $authCode->afterDelete();
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
