<?php
/**
 * AccessTokenService.php
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
use sweelix\oauth2\server\interfaces\AccessTokenModelInterface;
use sweelix\oauth2\server\interfaces\AccessTokenServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;
use yii\db\Query;

/**
 * This is the access token service for mySql
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\mySql
 * @since 1.0.0
 */
class AccessTokenService extends BaseService implements AccessTokenServiceInterface
{
    /**
     * @var string sql accessTokens table
     */
    public $accessTokensTable = null;

    /**
     * @var string sql scope accessToken table
     */
    public $scopeAccessTokenTable = null;

    /**
     * Save Access Token
     * @param AccessTokenModelInterface $accessToken
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     * @since 1.0.0
     */
    protected function insert(AccessTokenModelInterface $accessToken, $attributes)
    {
        $result = false;
        if (!$accessToken->beforeSave(true)) {
            return $result;
        }
        $accessTokenKey = $accessToken->getKey();
        $entity = (new Query())
            ->select('*')
            ->from($this->accessTokensTable)
            ->where('id = :id', [':id' => $accessTokenKey])
            ->one($this->db);
        if ($entity !== false) {
            throw new DuplicateKeyException('Duplicate key "' . $accessTokenKey . '"');
        }
        $values = $accessToken->getDirtyAttributes($attributes);
        $accessTokenParameters = [];
        $this->setAttributesDefinitions($accessToken->attributesDefinition());
        foreach ($values as $key => $value) {
            if (($value !== null) && ($key !== 'scopes')) {
                $accessTokenParameters[$key] = $this->convertToDatabase($key, $value);
            }
        }
        $accessTokenParameters['dateCreated'] = date('Y-m-d H:i:s');
        $accessTokenParameters['dateUpdated'] = date('Y-m-d H:i:s');
        try {
            $this->db->createCommand()
                ->insert($this->accessTokensTable, $accessTokenParameters)
                ->execute();
            if (!empty($values['scopes'])) {
                $values['scopes'] = array_unique($values['scopes']);
                foreach ($values['scopes'] as $scope) {
                    $scopeAccessTokenParams = [
                        'scopeId' => $scope,
                        'accessTokenId' => $accessTokenKey
                    ];
                    $this->db->createCommand()
                        ->insert($this->scopeAccessTokenTable, $scopeAccessTokenParams)
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
        if (isset($values[$modelKey]) === true) {
            $entity = (new Query())
                ->select('*')
                ->from($this->accessTokensTable)
                ->where('id = :id', [':id' => $values[$modelKey]])
                ->one($this->db);
            if ($entity !== false) {
                throw new DuplicateKeyException('Duplicate key "' . $values[$modelKey] . '"');
            }
        }
        $accessTokenKey = isset($values[$modelKey]) ? $values[$modelKey] : $accessToken->getKey();

        $accessTokenParameters = [];
        $this->setAttributesDefinitions($accessToken->attributesDefinition());
        foreach ($values as $key => $value) {
            if ($key !== 'scopes') {
                $accessTokenParameters[$key] = ($value !== null) ? $this->convertToDatabase($key, $value) : null;
            }
        }
        $accessTokenParameters['dateUpdated'] = date('Y-m-d H:i:s');
        try {
            if (array_key_exists($modelKey, $values) === true) {
                $oldAccessTokenKey = $accessToken->getOldKey();
                $this->db->createCommand()
                    ->update($this->accessTokensTable, $accessTokenParameters, 'id = :id', [':id' => $oldAccessTokenKey])
                    ->execute();
            } else {
                $this->db->createCommand()
                    ->update($this->accessTokensTable, $accessTokenParameters, 'id = :id', [':id' => $accessTokenKey])
                    ->execute();
            }
            if (isset($values['scopes'])) {
                $values['scopes'] = array_unique($values['scopes']);
                $scopeAccessTokens = (new Query())
                    ->select('*')
                    ->from($this->scopeAccessTokenTable)
                    ->where('accessTokenId = :accessTokenId', [':accessTokenId' => $accessTokenKey])
                    ->all($this->db);
                foreach ($scopeAccessTokens as $scopeAccessToken) {
                    if (($index = array_search($scopeAccessToken['scopeId'], $values['scopes'])) === false) {
                        $this->db->createCommand()
                            ->delete($this->scopeAccessTokenTable,
                                'accessTokenId = :accessTokenId AND scopeId = :scopeId',
                                [':accessTokenId' => $accessTokenKey, ':scopeId' => $scopeAccessToken['scopeId']])
                            ->execute();
                    } else {
                        unset($values['scopes'][$index]);
                    }
                }
                foreach ($values['scopes'] as $scope) {
                    $scopeAccessTokenParams = [
                        'scopeId' => $scope,
                        'accessTokenId' => $accessTokenKey
                    ];
                    $this->db->createCommand()
                        ->insert($this->scopeAccessTokenTable, $scopeAccessTokenParams)
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
     * @inheritdoc
     */
    public function findOne($key)
    {
        $record = null;
        $accessTokenData = (new Query())
            ->select('*')
            ->from($this->accessTokensTable)
            ->where('id = :id', [':id' => $key])
            ->one($this->db);

        if ($accessTokenData !== false) {
            $accessTokenData['scopes'] = [];
            $tmpScopes = (new Query())
                ->select('scopeId')
                ->from($this->scopeAccessTokenTable)
                ->all($this->db);
            foreach ($tmpScopes as $scope) {
                $accessTokenData['scopes'][] = $scope['scopeId'];
            }

            $record = Yii::createObject('sweelix\oauth2\server\interfaces\AccessTokenModelInterface');
            /** @var AccessTokenModelInterface $record */
            $properties = $record->attributesDefinition();
            $this->setAttributesDefinitions($properties);
            $attributes = [];
            foreach ($accessTokenData as $key => $value) {
                if (isset($properties[$key]) === true) {
                    $accessTokenData[$key] = $this->convertToModel($key, $value);
                    $record->setAttribute($key, $accessTokenData[$key]);
                    $attributes[$key] = $accessTokenData[$key];
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
    public function delete(AccessTokenModelInterface $accessToken)
    {
        $result = false;
        if ($accessToken->beforeDelete()) {
            //TODO: check results to return correct information
            $this->db->createCommand()
                ->delete($this->accessTokensTable, 'id = :id', [':id' => $accessToken->getKey()])
                ->execute();
            $accessToken->setIsNewRecord(true);
            $accessToken->afterDelete();
            $result = true;
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function findAllByUserId($userId)
    {
        $accessTokensList = (new Query())
            ->select('*')
            ->from($this->accessTokensTable)
            ->where('userId = :userId', [':userId' => $userId])
            ->all($this->db);
        $accessTokens = [];
        foreach ($accessTokensList as $accessToken) {
            $result = $this->findOne($accessToken['id']);
            if ($result instanceof AccessTokenModelInterface) {
                $accessTokens[] = $result;
            }
        }
        return $accessTokens;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllByUserId($userId)
    {
        $accessTokens = $this->findAllByUserId($userId);
        foreach ($accessTokens as $accessToken) {
            $this->delete($accessToken);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findAllByClientId($clientId)
    {
        $accessTokensList = (new Query())
            ->select('*')
            ->from($this->accessTokensTable)
            ->where('clientId = :clientId', [':clientId' => $clientId])
            ->all($this->db);
        $accessTokens = [];
        foreach ($accessTokensList as $accessToken) {
            $result = $this->findOne($accessToken['id']);
            if ($result instanceof AccessTokenModelInterface) {
                $accessTokens[] = $result;
            }
        }
        return $accessTokens;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllByClientId($clientId)
    {
        $accessTokens = $this->findAllByClientId($clientId);
        foreach ($accessTokens as $accessToken) {
            $this->delete($accessToken);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllExpired()
    {
        $accessTokens = (new Query())->select('*')
            ->from($this->accessTokensTable)
            ->where('expiry < :date', [':date' => date('Y-m-d H:i:s')])
            ->all();
        foreach ($accessTokens as $accessTokenQuery) {
            $accessToken = $this->findOne($accessTokenQuery['id']);
            if ($accessToken instanceof AccessTokenModelInterface) {
                $this->delete($accessToken);
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        $accessTokensList = (new Query())
            ->select('*')
            ->from($this->accessTokensTable)
            ->all($this->db);
        $accessTokens = [];
        foreach ($accessTokensList as $accessToken) {
            $result = $this->findOne($accessToken['id']);
            if ($result instanceof AccessTokenModelInterface) {
                $accessTokens[] = $result;
            }
        }
        return $accessTokens;
    }
}