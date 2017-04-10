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
 * @package sweelix\oauth2\server\services\redis
 */

namespace sweelix\oauth2\server\services\redis;

use sweelix\oauth2\server\exceptions\DuplicateIndexException;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
use sweelix\oauth2\server\interfaces\JwtModelInterface;
use sweelix\oauth2\server\interfaces\JwtServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;
use Exception;

/**
 * This is the jwt service for redis
 *  database structure
 *    * oauth2:jwt:<jid> : hash (Jwt)
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\redis
 * @since 1.0.0
 */
class JwtService extends BaseService implements JwtServiceInterface
{

    /**
     * @param string $jid jwt ID
     * @return string access token Key
     * @since 1.0.0
     */
    protected function getJwtKey($jid)
    {
        return $this->namespace . ':' . $jid;
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
        $jwtKey = $this->getJwtKey($jwt->getKey());
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
        $jwtId = isset($values[$modelKey]) ? $values[$modelKey] : $jwt->getKey();
        $jwtKey = $this->getJwtKey($jwtId);


        if (isset($values[$modelKey]) === true) {
            $newJwtKey = $this->getJwtKey($values[$modelKey]);
            $entityStatus = (int)$this->db->executeCommand('EXISTS', [$newJwtKey]);
            if ($entityStatus === 1) {
                throw new DuplicateKeyException('Duplicate key "'.$newJwtKey.'"');
            }
        }

        $this->db->executeCommand('MULTI');
        try {
            if (array_key_exists('id', $values) === true) {
                $oldId = $jwt->getOldKey();
                $oldJwtKey = $this->getJwtKey($oldId);

                $this->db->executeCommand('RENAMENX', [$oldJwtKey, $jwtKey]);
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
            $record = Yii::createObject('sweelix\oauth2\server\interfaces\JwtModelInterface');
            /** @var JwtModelInterface $record */
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
    public function delete(JwtModelInterface $jwt)
    {
        $result = false;
        if ($jwt->beforeDelete()) {
            $this->db->executeCommand('MULTI');
            $id = $jwt->getOldKey();
            $jwtKey = $this->getJwtKey($id);

            $this->db->executeCommand('DEL', [$jwtKey]);
            //TODO: check results to return correct information
            $queryResult = $this->db->executeCommand('EXEC');
            $jwt->setIsNewRecord(true);
            $jwt->afterDelete();
            $result = true;
        }
        return $result;
    }

}
