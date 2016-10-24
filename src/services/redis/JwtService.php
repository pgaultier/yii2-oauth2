<?php
/**
 * JwtService.php
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
use sweelix\oauth2\server\models\Jwt;
use sweelix\oauth2\server\interfaces\JwtServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;
use Exception;

/**
 * This is the jwt service for redis
 *  database structure
 *    * oauth2:jwt:<jid> : hash (Jwt)
 *    * oauth2:jwt:etags : hash <jid> -> <etag>
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\redis
 * @since XXX
 */
class JwtService extends BaseService implements JwtServiceInterface
{

    /**
     * @param string $jid jwt ID
     * @return string access token Key
     * @since XXX
     */
    protected function getJwtKey($jid)
    {
        return $this->namespace . ':' . $jid;
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
    public function save(Jwt $jwt, $attributes)
    {
        if ($jwt->getIsNewRecord()) {
            $result = $this->insert($jwt, $attributes);
        } else {
            $result = $this->update($jwt, $attributes);
        }
        return $result;
    }

    /**
     * Save Jwt
     * @param Jwt $jwt
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     * @since XXX
     */
    protected function insert(Jwt $jwt, $attributes)
    {
        $result = false;
        if (!$jwt->beforeSave(true)) {
            return $result;
        }
        $jwtKey = $this->getJwtKey($jwt->id);
        $etagKey = $this->getEtagIndexKey();
        //check if record exists
        $entityStatus = (int)$this->db->executeCommand('EXISTS', [$jwtKey]);
        if ($entityStatus === 1) {
            throw new DuplicateKeyException('Duplicate key "'.$jwtKey.'"');
        }

        $values = $jwt->getDirtyAttributes($attributes);
        $redisParameters = [$jwtKey];
        $this->setAttributesDefinitions($jwt->attributesDefinition());
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
                $etag = $this->computeEtag($jwt);
                $this->db->executeCommand('HSET', [$etagKey, $jwt->id, $etag]);
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
        $jwt->setOldAttributes($values);
        $jwt->afterSave(true, $changedAttributes);
        $result = true;
        return $result;
    }


    /**
     * Update Jwt
     * @param Jwt $jwt
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     */
    protected function update(Jwt $jwt, $attributes)
    {
        if (!$jwt->beforeSave(false)) {
            return false;
        }

        $etagKey = $this->getEtagIndexKey();
        $values = $jwt->getDirtyAttributes($attributes);
        $jwtId = isset($values['id']) ? $values['id'] : $jwt->id;
        $jwtKey = $this->getJwtKey($jwtId);


        if (isset($values['id']) === true) {
            $newJwtKey = $this->getJwtKey($values['id']);
            $entityStatus = (int)$this->db->executeCommand('EXISTS', [$newJwtKey]);
            if ($entityStatus === 1) {
                throw new DuplicateKeyException('Duplicate key "'.$newJwtKey.'"');
            }
        }

        $this->db->executeCommand('MULTI');
        try {
            if (array_key_exists('id', $values) === true) {
                $oldId = $jwt->getOldAttribute('id');
                $oldJwtKey = $this->getJwtKey($oldId);

                $this->db->executeCommand('RENAMENX', [$oldJwtKey, $jwtKey]);
                $this->db->executeCommand('HDEL', [$etagKey, $oldJwtKey]);
            }

            $redisUpdateParameters = [$jwtKey];
            $redisDeleteParameters = [$jwtKey];
            $this->setAttributesDefinitions($jwt->attributesDefinition());
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

            $etag = $this->computeEtag($jwt);
            $this->db->executeCommand('HSET', [$etagKey, $jwtId, $etag]);

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
    public function findOne($key)
    {
        $record = null;
        $accessTokenKey = $this->getJwtKey($key);
        $accessTokenExists = (bool)$this->db->executeCommand('EXISTS', [$accessTokenKey]);
        if ($accessTokenExists === true) {
            $accessTokenData = $this->db->executeCommand('HGETALL', [$accessTokenKey]);
            $record = Yii::createObject(Jwt::className());
            /** @var Jwt $record */
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
    public function delete(Jwt $jwt)
    {
        $result = false;
        if ($jwt->beforeDelete()) {
            $etagKey = $this->getEtagIndexKey();
            $this->db->executeCommand('MULTI');
            $id = $jwt->getOldKey();
            $accessTokenKey = $this->getJwtKey($id);

            $this->db->executeCommand('HDEL', [$etagKey, $id]);
            $this->db->executeCommand('DEL', [$accessTokenKey]);
            //TODO: check results to return correct information
            $queryResult = $this->db->executeCommand('EXEC');
            $jwt->setIsNewRecord(true);
            $jwt->afterDelete();
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
