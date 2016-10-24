<?php
/**
 * RefreshTokenService.php
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
use sweelix\oauth2\server\models\RefreshToken;
use sweelix\oauth2\server\interfaces\RefreshTokenServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;

/**
 * This is the refresh token service for redis
 *  database structure
 *    * oauth2:refreshTokens:<rid> : hash (RefreshToken)
 *    * oauth2:refreshTokens:etags : hash <rid> -> <etag>
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\redis
 * @since XXX
 */
class RefreshTokenService extends BaseService implements RefreshTokenServiceInterface
{

    /**
     * @param string $rid refresh token ID
     * @return string refresh token Key
     * @since XXX
     */
    protected function getRefreshTokenKey($rid)
    {
        return $this->namespace . ':' . $rid;
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
    public function save(RefreshToken $refreshToken, $attributes)
    {
        if ($refreshToken->getIsNewRecord()) {
            $result = $this->insert($refreshToken, $attributes);
        } else {
            $result = $this->update($refreshToken, $attributes);
        }
        return $result;
    }

    /**
     * Save Refresh Token
     * @param RefreshToken $refreshToken
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     * @since XXX
     */
    protected function insert(RefreshToken $refreshToken, $attributes)
    {
        $result = false;
        if (!$refreshToken->beforeSave(true)) {
            return $result;
        }
        $refreshTokenKey = $this->getRefreshTokenKey($refreshToken->id);
        $etagKey = $this->getEtagIndexKey();
        //check if record exists
        $entityStatus = (int)$this->db->executeCommand('EXISTS', [$refreshTokenKey]);
        if ($entityStatus === 1) {
            throw new DuplicateKeyException('Duplicate key "'.$refreshTokenKey.'"');
        }

        $values = $refreshToken->getDirtyAttributes($attributes);
        $redisParameters = [$refreshTokenKey];
        $this->setAttributesDefinitions($refreshToken->attributesDefinition());
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
                $etag = $this->computeEtag($refreshToken);
                $this->db->executeCommand('HSET', [$etagKey, $refreshToken->id, $etag]);
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
        $refreshToken->setOldAttributes($values);
        $refreshToken->afterSave(true, $changedAttributes);
        $result = true;
        return $result;
    }


    /**
     * Update Refresh Token
     * @param RefreshToken $refreshToken
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     */
    protected function update(RefreshToken $refreshToken, $attributes)
    {
        if (!$refreshToken->beforeSave(false)) {
            return false;
        }

        $etagKey = $this->getEtagIndexKey();
        $values = $refreshToken->getDirtyAttributes($attributes);
        $refreshTokenId = isset($values['id']) ? $values['id'] : $refreshToken->id;
        $refreshTokenKey = $this->getRefreshTokenKey($refreshTokenId);


        if (isset($values['id']) === true) {
            $newRefreshTokenKey = $this->getRefreshTokenKey($values['id']);
            $entityStatus = (int)$this->db->executeCommand('EXISTS', [$newRefreshTokenKey]);
            if ($entityStatus === 1) {
                throw new DuplicateKeyException('Duplicate key "'.$newRefreshTokenKey.'"');
            }
        }

        $this->db->executeCommand('MULTI');
        try {
            if (array_key_exists('id', $values) === true) {
                $oldId = $refreshToken->getOldAttribute('id');
                $oldRefreshTokenKey = $this->getRefreshTokenKey($oldId);

                $this->db->executeCommand('RENAMENX', [$oldRefreshTokenKey, $refreshTokenKey]);
                $this->db->executeCommand('HDEL', [$etagKey, $oldRefreshTokenKey]);
            }

            $redisUpdateParameters = [$refreshTokenKey];
            $redisDeleteParameters = [$refreshTokenKey];
            $this->setAttributesDefinitions($refreshToken->attributesDefinition());
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

            $etag = $this->computeEtag($refreshToken);
            $this->db->executeCommand('HSET', [$etagKey, $refreshTokenId, $etag]);

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
            $oldAttributes = $refreshToken->getOldAttributes();
            $changedAttributes[$name] = isset($oldAttributes[$name]) ? $oldAttributes[$name] : null;
            $refreshToken->setOldAttribute($name, $value);
        }
        $refreshToken->afterSave(false, $changedAttributes);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findOne($key)
    {
        $record = null;
        $refreshTokenKey = $this->getRefreshTokenKey($key);
        $refreshTokenExists = (bool)$this->db->executeCommand('EXISTS', [$refreshTokenKey]);
        if ($refreshTokenExists === true) {
            $refreshTokenData = $this->db->executeCommand('HGETALL', [$refreshTokenKey]);
            $record = Yii::createObject(RefreshToken::className());
            /** @var RefreshToken $record */
            $properties = $record->attributesDefinition();
            $this->setAttributesDefinitions($properties);
            $attributes = [];
            for ($i = 0; $i < count($refreshTokenData); $i += 2) {
                if (isset($properties[$refreshTokenData[$i]]) === true) {
                    $refreshTokenData[$i + 1] = $this->convertToModel($refreshTokenData[$i], $refreshTokenData[($i + 1)]);
                    $record->setAttribute($refreshTokenData[$i], $refreshTokenData[$i + 1]);
                    $attributes[$refreshTokenData[$i]] = $refreshTokenData[$i + 1];
                // @codeCoverageIgnoreStart
                } elseif ($record->canSetProperty($refreshTokenData[$i])) {
                    // TODO: find a way to test attribute population
                    $record->{$refreshTokenData[$i]} = $refreshTokenData[$i + 1];
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
    public function delete(RefreshToken $refreshToken)
    {
        $result = false;
        if ($refreshToken->beforeDelete()) {
            $etagKey = $this->getEtagIndexKey();
            $this->db->executeCommand('MULTI');
            $id = $refreshToken->getOldKey();
            $refreshTokenKey = $this->getRefreshTokenKey($id);

            $this->db->executeCommand('HDEL', [$etagKey, $id]);
            $this->db->executeCommand('DEL', [$refreshTokenKey]);
            //TODO: check results to return correct information
            $queryResult = $this->db->executeCommand('EXEC');
            $refreshToken->setIsNewRecord(true);
            $refreshToken->afterDelete();
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
