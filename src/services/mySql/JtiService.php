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
 * @package sweelix\oauth2\server\services\mySql
 */

namespace sweelix\oauth2\server\services\mySql;

use sweelix\oauth2\server\exceptions\DuplicateIndexException;
use sweelix\oauth2\server\exceptions\DuplicateKeyException;
use sweelix\oauth2\server\interfaces\JtiModelInterface;
use sweelix\oauth2\server\interfaces\JtiServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;
use yii\db\Query;

/**
 * This is the jti service for mySql
 *  database structure
 *    * oauth2:jti:<jid> : hash (Jti)
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\mySql
 * @since 1.0.0
 */
class JtiService extends BaseService implements JtiServiceInterface
{
    /**
     * @var string sql jtis table
     */
    public $jtisTable = null;

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
        $jtiKey = $jti->getKey();
        $entity = (new Query())
            ->select('*')
            ->from($this->jtisTable)
            ->where('id = :id', [':id' => $jtiKey])
            ->one($this->db);
        if ($entity !== false) {
            throw new DuplicateKeyException('Duplicate key "' . $jtiKey . '"');
        }
        $values = $jti->getDirtyAttributes($attributes);
        $jtisParameters = [];
        $this->setAttributesDefinitions($jti->attributesDefinition());
        foreach ($values as $key => $value) {
            if ($value !== null) {
                $jtisParameters[$key] = $this->convertToDatabase($key, $value);
            }
        }
        $jtisParameters['dateCreated'] = date('Y-m-d H:i:s');
        $jtisParameters['dateUpdated'] = date('Y-m-d H:i:s');
        try {
            $this->db->createCommand()
                ->insert($this->jtisTable, $jtisParameters)
                ->execute();
        } catch (DatabaseException $e) {
            // @codeCoverageIgnoreStart
            // we have a MYSQL exception, we should not discard
            Yii::debug('Error while inserting entity', __METHOD__);
            throw $e;
            // @codeCoverageIgnoreEnd
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
        if (isset($values[$modelKey]) === true) {
            $entity = (new Query())
                ->select('*')
                ->from($this->jtisTable)
                ->where('id = :id', [':id' => $values[$modelKey]])
                ->one($this->db);
            if ($entity !== false) {
                throw new DuplicateKeyException('Duplicate key "' . $values[$modelKey] . '"');
            }
        }
        $jtiKey = isset($values[$modelKey]) ? $values[$modelKey] : $jti->getKey();

        $jtiParameters = [];
        $this->setAttributesDefinitions($jti->attributesDefinition());
        foreach ($values as $key => $value) {
            $jtiParameters[$key] = ($value !== null) ? $this->convertToDatabase($key, $value) : null;
        }
        $jtiParameters['dateUpdated'] = date('Y-m-d H:i:s');
        try {
            if (array_key_exists($modelKey, $values) === true) {
                $oldJtiKey = $jti->getOldKey();
                $this->db->createCommand()
                    ->update($this->jtisTable, $jtiParameters, 'id = :id', [':id' => $oldJtiKey])
                    ->execute();
            } else {
                $this->db->createCommand()
                    ->update($this->jtisTable, $jtiParameters, 'id = :id', [':id' => $jtiKey])
                    ->execute();
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
     * @inheritdoc
     */
    public function findOne($key)
    {
        $record = null;
        $jtiData = (new Query())
            ->select('*')
            ->from($this->jtisTable)
            ->where('id = :id', [':id' => $key])
            ->one($this->db);
        if ($jtiData !== false) {
            $record = Yii::createObject('sweelix\oauth2\server\interfaces\JtiModelInterface');
            /** @var JtiModelInterface $record */
            $properties = $record->attributesDefinition();
            $this->setAttributesDefinitions($properties);
            $attributes = [];
            foreach ($jtiData as $key => $value) {
                if (isset($properties[$key]) === true) {
                    $jtiData[$key] = $this->convertToModel($key, $value);
                    $record->setAttribute($key, $jtiData[$key]);
                    $attributes[$key] = $jtiData[$key];
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
    public function delete(JtiModelInterface $jti)
    {
        $result = false;
        if ($jti->beforeDelete()) {
            //TODO: check results to return correct information
            $this->db->createCommand()
                ->delete($this->jtisTable, 'id = :id', [':id' => $jti->getKey()])
                ->execute();
            $jti->setIsNewRecord(true);
            $jti->afterDelete();
            $result = true;
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function findAllBySubject($subject)
    {
        $jtisList = (new Query())
            ->select('*')
            ->from($this->jtisTable)
            ->where('subject = :subject', [':subject' => $subject])
            ->all($this->db);
        $jtis = [];
        foreach ($jtisList as $jti) {
            $result = $this->findOne($jti['id']);
            if ($result instanceof JtiModelInterface) {
                $jtis[] = $result;
            }
        }
        return $jtis;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllBySubject($subject)
    {
        $jtis = $this->findAllBySubject($subject);
        foreach ($jtis as $jti) {
            $this->delete($jti);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findAllByClientId($clientId)
    {
        $jtisList = (new Query())
            ->select('*')
            ->from($this->jtisTable)
            ->where('clientId = :clientId', [':clientId' => $clientId])
            ->all($this->db);
        $jtis = [];
        foreach ($jtisList as $jti) {
            $result = $this->findOne($jti['id']);
            if ($result instanceof JtiModelInterface) {
                $jtis[] = $result;
            }
        }
        return $jtis;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllByClientId($clientId)
    {
        $jtis = $this->findAllByClientId($clientId);
        foreach ($jtis as $jti) {
            $this->delete($jti);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllExpired()
    {
        $jtis = (new Query())->select('*')
            ->from($this->jtisTable)
            ->where('expires < :date', [':date' => date('Y-m-d H:i:s')])
            ->all($this->db);
        foreach ($jtis as $jtiQuery) {
            $jti = $this->findOne($jtiQuery['id']);
            if ($jti instanceof JtiModelInterface) {
                $this->delete($jti);
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        $jtisList = (new Query())
            ->select('*')
            ->from($this->jtisTable)
            ->all($this->db);
        $jtis = [];
        foreach ($jtisList as $jti) {
            $result = $this->findOne($jti['id']);
            if ($result instanceof JtiModelInterface) {
                $jtis[] = $result;
            }
        }
        return $jtis;
    }
}