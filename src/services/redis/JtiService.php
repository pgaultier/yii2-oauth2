<?php
/**
 * JtiService.php
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
use sweelix\oauth2\server\models\Jti;
use sweelix\oauth2\server\interfaces\JtiServiceInterface;
use yii\db\Exception as DatabaseException;
use Yii;

/**
 * This is the jti service for redis
 *  database structure
 *    * oauth2:jti:<jid> : hash (Jti)
 *    * oauth2:jti:etags : hash <jid> -> <etag>
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\services\redis
 * @since XXX
 */
class JtiService extends BaseService implements JtiServiceInterface
{

    /**
     * @return string jti Key
     * @since XXX
     */
    protected function getJtiKey()
    {
        return $this->namespace;
    }

    /**
     * @inheritdoc
     */
    public function save(Jti $jti, $attributes)
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
     * @param Jti $jti
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     * @since XXX
     */
    protected function insert(Jti $jti, $attributes)
    {
        $result = false;
        if (!$jti->beforeSave(true)) {
            return $result;
        }
        $jtiKey = $this->getJtiKey();

        $values = $jti->getDirtyAttributes($attributes);
        $memberKey = $this->createMember($values);
        $this->db->executeCommand('SADD', [$jtiKey, $memberKey]);

        $changedAttributes = array_fill_keys(array_keys($values), null);
        $jti->setOldAttributes($values);
        $jti->afterSave(true, $changedAttributes);
        $result = true;
        return $result;
    }


    /**
     * Update Jti
     * @param Jti $jti
     * @param null|array $attributes attributes to save
     * @return bool
     * @throws DatabaseException
     * @throws DuplicateIndexException
     * @throws DuplicateKeyException
     */
    protected function update(Jti $jti, $attributes)
    {
        if (!$jti->beforeSave(false)) {
            return false;
        }

        $jtiKey = $this->getJtiKey();

        $values = $jti->getDirtyAttributes($attributes);

        $oldMember = $this->createMember($jti->getOldAttributes());
        $newMember = $this->createMember($jti->getAttributes());

        $this->db->executeCommand('MULTI');
        try {
            $this->db->executeCommand('SREM', [$jtiKey, $oldMember]);
            $this->db->executeCommand('SADD', [$jtiKey, $newMember]);
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
    public function findOne($issuer, $subject, $audience, $expires, $jti)
    {
        $record = null;
        $jtiKey = $this->getJtiKey();
        $memberKey = $issuer .':'. $subject .':'. $audience .':'. $expires .':'. $jti;
        $exist = $this->db->executeCommand('SISMEMBER', [$memberKey]);
        if ($exist == 1) {
            $record = Yii::createObject(Jti::className());
            /** @var Jti $record */
            $record->issuer = $issuer;
            $record->subject = $subject;
            $record->audience = $audience;
            $record->expires = $expires;
            $record->jti = $jti;
            $record->setOldAttributes([
                'issuer' => $issuer,
                'subject' => $subject,
                'audience' => $audience,
                'expires' => $expires,
                'jti' => $jti,
            ]);
            $record->afterFind();
        }
        return $record;
    }

    /**
     * @inheritdoc
     */
    public function delete(Jti $jti)
    {
        $result = false;
        if ($jti->beforeDelete()) {

            $member = $this->createMember($jti->getAttributes());
            $jtiKey = $this->getJtiKey();
            $this->db->executeCommand('SREM', [$jtiKey, $member]);

            $jti->setIsNewRecord(true);
            $jti->afterDelete();
            $result = true;
        }
        return $result;
    }

    /**
     * @param Jti $jti
     * @return string
     */
    /*
    protected function createMember(Jti $jti)
    {
        return $jti->issuer.':'.$jti->subject.':'.$jti->audience.':'.$jti->expires.':'.$jti->jti;
    }
    */

    /**
     * @param array $jtiValues
     * @return string
     */
    protected function createMember($jtiValues)
    {
        return $jtiValues['issuer'].':'.$jtiValues['subject'].':'.$jtiValues['audience'].':'.$jtiValues['expires'].':'.$jtiValues['jti'];
    }
}
