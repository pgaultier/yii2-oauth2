<?php
/**
 * AuthCodeService.php
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
use sweelix\oauth2\server\interfaces\AuthCodeModelInterface;
use sweelix\oauth2\server\interfaces\AuthCodeServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * This is the auth code service for mySql
 *  database structure
 *    * oauth2:authCodes:<aid> : hash (AuthCode)
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\mySql
 * @since 1.0.0
 */
class AuthCodeService extends BaseService implements AuthCodeServiceInterface
{
    /**
     * @var string sql authorizationCodes table
     */
    public $authorizationCodesTable = null;

    /**
     * @var string sql scope authorizationCode table
     */
    public $scopeAuthorizationCodeTable = null;

    /**
     * Save Auth Code
     * @param AuthCodeModelInterface $authCode
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     * @since 1.0.0
     */
    protected function insert(AuthCodeModelInterface $authCode, $attributes)
    {
        $result = false;
        if (!$authCode->beforeSave(true)) {
            return $result;
        }
        $authCodeKey = $authCode->getKey();
        $entity = (new Query())
            ->select('*')
            ->from($this->authorizationCodesTable)
            ->where('id = :id', [':id' => $authCodeKey])
            ->one($this->db);
        if ($entity !== false) {
            throw new DuplicateKeyException('Duplicate key "' . $authCodeKey . '"');
        }
        $values = $authCode->getDirtyAttributes($attributes);
        $authCodeParameters = [];
        $this->setAttributesDefinitions($authCode->attributesDefinition());
        foreach ($values as $key => $value) {
            if (($value !== null) && ($key !== 'scopes')) {
                $authCodeParameters[$key] = $this->convertToDatabase($key, $value);
            }
        }
        $authCodeParameters['dateCreated'] = new Expression('NOW()');
        $authCodeParameters['dateUpdated'] = new Expression('NOW()');
        try {
            $this->db->createCommand()
                ->insert($this->authorizationCodesTable, $authCodeParameters)
                ->execute();
            if (!empty($values['scopes'])) {
                $values['scopes'] = array_unique($values['scopes']);
                foreach ($values['scopes'] as $scope) {
                    $scopeAuthCodeParams = [
                        'scopeId' => $scope,
                        'authorizationCodeId' => $authCodeKey
                    ];
                    $this->db->createCommand()
                        ->insert($this->scopeAuthorizationCodeTable, $scopeAuthCodeParams)
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

        $values = $authCode->getDirtyAttributes($attributes);
        $modelKey = $authCode->key();
        if (isset($values[$modelKey]) === true) {
            $entity = (new Query())
                ->select('*')
                ->from($this->authorizationCodesTable)
                ->where('id = :id', [':id' => $values[$modelKey]])
                ->one($this->db);
            if ($entity !== false) {
                throw new DuplicateKeyException('Duplicate key "' . $values[$modelKey] . '"');
            }
        }
        $authCodeKey = isset($values[$modelKey]) ? $values[$modelKey] : $authCode->getKey();

        $authCodeParameters = [];
        $this->setAttributesDefinitions($authCode->attributesDefinition());
        foreach ($values as $key => $value) {
            if ($key !== 'scopes') {
                $authCodeParameters[$key] = ($value !== null) ? $this->convertToDatabase($key, $value) : null;
            }
        }
        $authCodeParameters['dateUpdated'] = new Expression('NOW()');
        try {
            if (array_key_exists($modelKey, $values) === true) {
                $oldAuthCodeKey = $authCode->getOldKey();
                $this->db->createCommand()
                    ->update($this->authorizationCodesTable, $authCodeParameters, 'id = :id', [':id' => $oldAuthCodeKey])
                    ->execute();
            } else {
                $this->db->createCommand()
                    ->update($this->authorizationCodesTable, $authCodeParameters, 'id = :id', [':id' => $authCodeKey])
                    ->execute();
            }
            if (isset($values['scopes'])) {
                $values['scopes'] = array_unique($values['scopes']);
                $scopeAuthCodes = (new Query())
                    ->select('*')
                    ->from($this->scopeAuthorizationCodeTable)
                    ->where('authorizationCodeId = :authorizationCodeId', [':authorizationCodeId' => $authCodeKey])
                    ->all($this->db);
                foreach ($scopeAuthCodes as $scopeAuthCode) {
                    if (($index = array_search($scopeAuthCode['scopeId'], $values['scopes'])) === false) {
                        $this->db->createCommand()
                            ->delete($this->scopeAuthorizationCodeTable,
                                'authorizationCodeId = :authorizationCodeId AND scopeId = :scopeId',
                                [':authorizationCodeId' => $authCodeKey, ':scopeId' => $scopeAuthCode['scopeId']])
                            ->execute();
                    } else {
                        unset($values['scopes'][$index]);
                    }
                }
                foreach ($values['scopes'] as $scope) {
                    $scopeAuthCodeParams = [
                        'scopeId' => $scope,
                        'authorizationCodeId' => $authCodeKey
                    ];
                    $this->db->createCommand()
                        ->insert($this->scopeAuthorizationCodeTable, $scopeAuthCodeParams)
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
     * @inheritdoc
     */
    public function findOne($key)
    {
        $record = null;
        $authCodeData = (new Query())
            ->select('*')
            ->from($this->authorizationCodesTable)
            ->where('id = :id', [':id' => $key])
            ->one($this->db);

        if ($authCodeData !== false) {
            $authCodeData['scopes'] = [];
            $tmpScopes = (new Query())
                ->select('scopeId')
                ->from($this->scopeAuthorizationCodeTable)
                ->all($this->db);
            foreach ($tmpScopes as $scope) {
                $authCodeData['scopes'][] = $scope['scopeId'];
            }

            $record = Yii::createObject('sweelix\oauth2\server\interfaces\AuthCodeModelInterface');
            /** @var AuthCodeModelInterface $record */
            $properties = $record->attributesDefinition();
            $this->setAttributesDefinitions($properties);
            $attributes = [];
            foreach ($authCodeData as $key => $value) {
                if (isset($properties[$key]) === true) {
                    $authCodeData[$key] = $this->convertToModel($key, $value);
                    $record->setAttribute($key, $authCodeData[$key]);
                    $attributes[$key] = $authCodeData[$key];
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
    public function delete(AuthCodeModelInterface $authCode)
    {
        $result = false;
        if ($authCode->beforeDelete()) {
            //TODO: check results to return correct information
            $this->db->createCommand()
                ->delete($this->authorizationCodesTable, 'id = :id', [':id' => $authCode->getKey()])
                ->execute();
            $authCode->setIsNewRecord(true);
            $authCode->afterDelete();
            $result = true;
        }
        return $result;
    }
}