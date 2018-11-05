<?php
/**
 * RefreshTokenService.php
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
use sweelix\oauth2\server\interfaces\RefreshTokenModelInterface;
use sweelix\oauth2\server\interfaces\RefreshTokenServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;
use yii\db\Query;

/**
 * This is the refresh token service for mySql
 *  database structure
 *    * oauth2:refreshTokens:<rid> : hash (RefreshToken)
 *    * oauth2:users:<uid>:refreshTokens : set (RefreshTokens for user)
 *    * oauth2:clients:<cid>:refreshTokens : set (RefreshTokens for client)
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\mySql
 * @since 1.0.0
 */
class RefreshTokenService extends BaseService implements RefreshTokenServiceInterface
{
    /**
     * @var string sql refreshTokens table
     */
    public $refreshTokensTable = null;

    /**
     * @var string sql scope refreshToken table
     */
    public $scopeRefreshTokenTable = null;

    /**
     * Save Refresh Token
     * @param RefreshTokenModelInterface $refreshToken
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     * @since 1.0.0
     */
    protected function insert(RefreshTokenModelInterface $refreshToken, $attributes)
    {
        $result = false;
        if (!$refreshToken->beforeSave(true)) {
            return $result;
        }
        $refreshTokenKey = $refreshToken->getKey();
        $entity = (new Query())
            ->select('*')
            ->from($this->refreshTokensTable)
            ->where('id = :id', [':id' => $refreshTokenKey])
            ->one($this->db);
        if ($entity !== false) {
            throw new DuplicateKeyException('Duplicate key "' . $refreshTokenKey . '"');
        }
        $values = $refreshToken->getDirtyAttributes($attributes);
        $refreshTokenParameters = [];
        $this->setAttributesDefinitions($refreshToken->attributesDefinition());
        foreach ($values as $key => $value) {
            if (($value !== null) && ($key !== 'scopes')) {
                $refreshTokenParameters[$key] = $this->convertToDatabase($key, $value);
            }
        }
        $refreshTokenParameters['dateCreated'] = date('Y-m-d H:i:s');
        $refreshTokenParameters['dateUpdated'] = date('Y-m-d H:i:s');
        try {
            $this->db->createCommand()
                ->insert($this->refreshTokensTable, $refreshTokenParameters)
                ->execute();
            if (!empty($values['scopes'])) {
                $values['scopes'] = array_unique($values['scopes']);
                foreach ($values['scopes'] as $scope) {
                    $scopeAccessTokenParams = [
                        'scopeId' => $scope,
                        'refreshTokenId' => $refreshTokenKey
                    ];
                    $this->db->createCommand()
                        ->insert($this->scopeRefreshTokenTable, $scopeAccessTokenParams)
                        ->execute();
                }
            }
        } catch (DatabaseException $e) {
            // @codeCoverageIgnoreStart
            // we have a MYSQL exception, we should not discard
            Yii::debug('Error while inserting entity', __METHOD__);
            throw $e;
            // @codeCoverageIgnoreEnd
        }
        $changedAttributes = array_fill_keys(array_keys($values), null);
        $refreshToken->setOldAttributes($values);
        $refreshToken->afterSave(true, $changedAttributes);
        $result = true;
        return $result;
    }

    /**
     * Update Refresh Token
     * @param RefreshTokenModelInterface $refreshToken
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     */
    protected function update(RefreshTokenModelInterface $refreshToken, $attributes)
    {
        if (!$refreshToken->beforeSave(false)) {
            return false;
        }

        $values = $refreshToken->getDirtyAttributes($attributes);
        $modelKey = $refreshToken->key();
        if (isset($values[$modelKey]) === true) {
            $entity = (new Query())
                ->select('*')
                ->from($this->refreshTokensTable)
                ->where('id = :id', [':id' => $values[$modelKey]])
                ->one($this->db);
            if ($entity !== false) {
                throw new DuplicateKeyException('Duplicate key "' . $values[$modelKey] . '"');
            }
        }
        $refreshTokenKey = isset($values[$modelKey]) ? $values[$modelKey] : $refreshToken->getKey();

        $refreshTokenParameters = [];
        $this->setAttributesDefinitions($refreshToken->attributesDefinition());
        foreach ($values as $key => $value) {
            if ($key !== 'scopes') {
                $refreshTokenParameters[$key] = ($value !== null) ? $this->convertToDatabase($key, $value) : null;
            }
        }
        $refreshTokenParameters['dateUpdated'] = date('Y-m-d H:i:s');
        try {
            if (array_key_exists($modelKey, $values) === true) {
                $oldRefreshTokenKey = $refreshToken->getOldKey();
                $this->db->createCommand()
                    ->update($this->refreshTokensTable, $refreshTokenParameters, 'id = :id', [':id' => $oldRefreshTokenKey])
                    ->execute();
            } else {
                $this->db->createCommand()
                    ->update($this->refreshTokensTable, $refreshTokenParameters, 'id = :id', [':id' => $refreshTokenKey])
                    ->execute();
            }
            if (isset($values['scopes'])) {
                $values['scopes'] = array_unique($values['scopes']);
                $scopeRefreshTokens = (new Query())
                    ->select('*')
                    ->from($this->scopeRefreshTokenTable)
                    ->where('refreshTokenId = :refreshTokenId', [':refreshTokenId' => $refreshTokenKey])
                    ->all($this->db);
                foreach ($scopeRefreshTokens as $scopeRefreshToken) {
                    if (($index = array_search($scopeRefreshToken['scopeId'], $values['scopes'])) === false) {
                        $this->db->createCommand()
                            ->delete($this->scopeRefreshTokenTable,
                                'refreshTokenId = :refreshTokenId AND scopeId = :scopeId',
                                [':refreshTokenId' => $refreshTokenKey, ':scopeId' => $scopeRefreshToken['scopeId']])
                            ->execute();
                    } else {
                        unset($values['scopes'][$index]);
                    }
                }
                foreach ($values['scopes'] as $scope) {
                    $scopeRefreshTokenParams = [
                        'scopeId' => $scope,
                        'refreshTokenId' => $refreshTokenKey
                    ];
                    $this->db->createCommand()
                        ->insert($this->scopeRefreshTokenTable, $scopeRefreshTokenParams)
                        ->execute();
                }
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
    public function save(RefreshTokenModelInterface $refreshToken, $attributes)
    {
        if ($refreshToken->getIsNewRecord()) {
            $result = $this->insert($refreshToken, $attributes);
        } else {
            $result = $this->update($refreshToken, $attributes);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function findOne($key)
    {
        $record = null;
        $refreshTokenData = (new Query())
            ->select('*')
            ->from($this->refreshTokensTable)
            ->where('id = :id', [':id' => $key])
            ->one($this->db);

        if ($refreshTokenData !== false) {
            $refreshTokenData['scopes'] = [];
            $tmpScopes = (new Query())
                ->select('scopeId')
                ->from($this->scopeRefreshTokenTable)
                ->all($this->db);
            foreach ($tmpScopes as $scope) {
                $refreshTokenData['scopes'][] = $scope['scopeId'];
            }

            $record = Yii::createObject('sweelix\oauth2\server\interfaces\RefreshTokenModelInterface');
            /** @var RefreshTokenModelInterface $record */
            $properties = $record->attributesDefinition();
            $this->setAttributesDefinitions($properties);
            $attributes = [];
            foreach ($refreshTokenData as $key => $value) {
                if (isset($properties[$key]) === true) {
                    $refreshTokenData[$key] = $this->convertToModel($key, $value);
                    $record->setAttribute($key, $refreshTokenData[$key]);
                    $attributes[$key] = $refreshTokenData[$key];
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
    public function delete(RefreshTokenModelInterface $refreshToken)
    {
        $result = false;
        if ($refreshToken->beforeDelete()) {
            //TODO: check results to return correct information
            $this->db->createCommand()
                ->delete($this->refreshTokensTable, 'id = :id', [':id' => $refreshToken->getKey()])
                ->execute();
            $refreshToken->setIsNewRecord(true);
            $refreshToken->afterDelete();
            $result = true;
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function findAllByUserId($userId)
    {
        $refreshTokensList = (new Query())
            ->select('*')
            ->from($this->refreshTokensTable)
            ->where('userId = :userId', [':userId' => $userId])
            ->all($this->db);
        $refreshTokens = [];
        foreach ($refreshTokensList as $refreshToken) {
            $result = $this->findOne($refreshToken['id']);
            if ($result instanceof RefreshTokenModelInterface) {
                $refreshTokens[] = $result;
            }
        }
        return $refreshTokens;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllByUserId($userId)
    {
        $refreshTokens = $this->findAllByUserId($userId);
        foreach ($refreshTokens as $refreshToken) {
            $this->delete($refreshToken);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findAllByClientId($clientId)
    {
        $refreshTokensList = (new Query())
            ->select('*')
            ->from($this->refreshTokensTable)
            ->where('clientId = :clientId', [':clientId' => $clientId])
            ->all($this->db);
        $refreshTokens = [];
        foreach ($refreshTokensList as $refreshToken) {
            $result = $this->findOne($refreshToken['id']);
            if ($result instanceof RefreshTokenModelInterface) {
                $refreshTokens[] = $result;
            }
        }
        return $refreshTokens;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllByClientId($clientId)
    {
        $refreshTokens = $this->findAllByClientId($clientId);
        foreach ($refreshTokens as $refreshToken) {
            $this->delete($refreshToken);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllExpired()
    {
        $refreshTokens = (new Query())->select('*')
            ->from($this->refreshTokensTable)
            ->where('expiry < :date', [':date' => date('Y-m-d H:i:s')])
            ->all($this->db);
        foreach ($refreshTokens as $refreshTokenQuery) {
            $refreshToken = $this->findOne($refreshTokenQuery['id']);
            if ($refreshToken instanceof RefreshTokenModelInterface) {
                $this->delete($refreshToken);
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        $refreshTokensList = (new Query())
            ->select('*')
            ->from($this->refreshTokensTable)
            ->all($this->db);
        $refreshTokens = [];
        foreach ($refreshTokensList as $refreshToken) {
            $result = $this->findOne($refreshToken['id']);
            if ($result instanceof RefreshTokenModelInterface) {
                $refreshTokens[] = $result;
            }
        }
        return $refreshTokens;
    }
}