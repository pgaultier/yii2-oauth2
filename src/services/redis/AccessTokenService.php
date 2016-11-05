<?php
/**
 * AccessTokenService.php
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
use sweelix\oauth2\server\interfaces\AccessTokenModelInterface;
use sweelix\oauth2\server\interfaces\AccessTokenServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;

/**
 * This is the access token service for redis
 *  database structure
 *    * oauth2:accessTokens:<aid> : hash (AccessToken)
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\redis
 * @since XXX
 */
class AccessTokenService extends BaseService implements AccessTokenServiceInterface
{

    /**
     * @param string $aid access token ID
     * @return string access token Key
     * @since XXX
     */
    protected function getAccessTokenKey($aid)
    {
        return $this->namespace . ':' . $aid;
    }

    /**
     * @inheritdoc
     */
    public function save(AccessTokenModelInterface $accessToken, $attributes)
    {
        if ($accessToken->getIsNewRecord()) {
            $result = $this->insert($accessToken, $attributes);
        } else {
            $result = $this->update($accessToken, $attributes);
        }
        return $result;
    }

    /**
     * Save Access Token
     * @param AccessTokenModelInterface $accessToken
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     * @since XXX
     */
    protected function insert(AccessTokenModelInterface $accessToken, $attributes)
    {
        $result = false;
        if (!$accessToken->beforeSave(true)) {
            return $result;
        }
        $accessTokenKey = $this->getAccessTokenKey($accessToken->getKey());
        //check if record exists
        $entityStatus = (int)$this->db->executeCommand('EXISTS', [$accessTokenKey]);
        if ($entityStatus === 1) {
            throw new DuplicateKeyException('Duplicate key "'.$accessTokenKey.'"');
        }

        $values = $accessToken->getDirtyAttributes($attributes);
        $redisParameters = [$accessTokenKey];
        $this->setAttributesDefinitions($accessToken->attributesDefinition());
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
        $accessToken->setOldAttributes($values);
        $accessToken->afterSave(true, $changedAttributes);
        $result = true;
        return $result;
    }


    /**
     * Update Access Token
     * @param AccessTokenModelInterface $accessToken
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     */
    protected function update(AccessTokenModelInterface $accessToken, $attributes)
    {
        if (!$accessToken->beforeSave(false)) {
            return false;
        }

        $values = $accessToken->getDirtyAttributes($attributes);
        $modelKey = $accessToken->key();
        $accessTokenId = isset($values[$modelKey]) ? $values[$modelKey] : $accessToken->getKey();
        $accessTokenKey = $this->getAccessTokenKey($accessTokenId);


        if (isset($values[$modelKey]) === true) {
            $newAccessTokenKey = $this->getAccessTokenKey($values[$modelKey]);
            $entityStatus = (int)$this->db->executeCommand('EXISTS', [$newAccessTokenKey]);
            if ($entityStatus === 1) {
                throw new DuplicateKeyException('Duplicate key "'.$newAccessTokenKey.'"');
            }
        }

        $this->db->executeCommand('MULTI');
        try {
            if (array_key_exists($modelKey, $values) === true) {
                $oldId = $accessToken->getOldKey();
                $oldAccessTokenKey = $this->getAccessTokenKey($oldId);

                $this->db->executeCommand('RENAMENX', [$oldAccessTokenKey, $accessTokenKey]);
            }

            $redisUpdateParameters = [$accessTokenKey];
            $redisDeleteParameters = [$accessTokenKey];
            $this->setAttributesDefinitions($accessToken->attributesDefinition());
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
            $oldAttributes = $accessToken->getOldAttributes();
            $changedAttributes[$name] = isset($oldAttributes[$name]) ? $oldAttributes[$name] : null;
            $accessToken->setOldAttribute($name, $value);
        }
        $accessToken->afterSave(false, $changedAttributes);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findOne($key)
    {
        $record = null;
        $accessTokenKey = $this->getAccessTokenKey($key);
        $accessTokenExists = (bool)$this->db->executeCommand('EXISTS', [$accessTokenKey]);
        if ($accessTokenExists === true) {
            $accessTokenData = $this->db->executeCommand('HGETALL', [$accessTokenKey]);
            $record = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
            /** @var AccessTokenModelInterface $record */
            $properties = $record->attributesDefinition();
            $this->setAttributesDefinitions($properties);
            $attributes = [];
            for ($i = 0; $i < count($accessTokenData); $i += 2) {
                if (isset($properties[$accessTokenData[$i]]) === true) {
                    $accessTokenData[$i + 1] = $this->convertToModel($accessTokenData[$i], $accessTokenData[($i + 1)]);
                    $record->setAttribute($accessTokenData[$i], $accessTokenData[$i + 1]);
                    $attributes[$accessTokenData[$i]] = $accessTokenData[$i + 1];
                // @codeCoverageIgnoreStart
                } elseif ($record->canSetProperty($accessTokenData[$i])) {
                    // TODO: find a way to test attribute population
                    $record->{$accessTokenData[$i]} = $accessTokenData[$i + 1];
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
    public function delete(AccessTokenModelInterface $accessToken)
    {
        $result = false;
        if ($accessToken->beforeDelete()) {
            $this->db->executeCommand('MULTI');
            $id = $accessToken->getOldKey();
            $accessTokenKey = $this->getAccessTokenKey($id);

            $this->db->executeCommand('DEL', [$accessTokenKey]);
            //TODO: check results to return correct information
            $queryResult = $this->db->executeCommand('EXEC');
            $accessToken->setIsNewRecord(true);
            $accessToken->afterDelete();
            $result = true;
        }
        return $result;
    }

}
