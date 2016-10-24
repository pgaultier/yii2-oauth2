<?php
/**
 * Jti.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @since XXX
 */

namespace sweelix\oauth2\server\models;

use Yii;

/**
 * This is the jti model
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\models
 * @since XXX
 *
 * @property string $issuer
 * @property string $subject
 * @property string $audience
 * @property string $expires
 * @property string $jti
 */
class Jti extends BaseModel
{

    /**
     * @return \sweelix\oauth2\server\interfaces\JtiServiceInterface
     */
    protected static function getDataService()
    {
        return Yii::createObject('sweelix\oauth2\server\interfaces\JtiServiceInterface');
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return 'id';
    }

    /**
     * @return array definition of model attributes
     * @since XXX
     */
    public function attributesDefinition()
    {
        return [
            'issuer' => 'string',
            'subject' => 'string',
            'audience' => 'string',
            'expires' => 'string',
            'jti' => 'string',
        ];
    }

    /**
     * Find one jti by its key
     *
     * @param string $issuer
     * @param string $subject
     * @param string $audience
     * @param string $expires
     * @param string $jti
     * @return Jti|null
     * @since XXX
     * @throws \yii\base\UnknownClassException
     */
    public static function findOne($issuer, $subject, $audience, $expires, $jti)
    {
        return self::getDataService()->findOne($issuer, $subject, $audience, $expires, $jti);
    }

    /**
     * @param bool $runValidation
     * @param null $attributes
     * @return bool
     * @since XXX
     * @throws \yii\base\UnknownClassException
     */
    public function save($runValidation = true, $attributes = null)
    {
        if ($runValidation && !$this->validate($attributes)) {
            Yii::info('Model not inserted due to validation error.', __METHOD__);
            $result = false;
        } else {
            $result = self::getDataService()->save($this, $attributes);
        }
        return $result;
    }

    /**
     * @return bool
     * @since XXX
     * @throws \yii\base\UnknownClassException
     */
    public function delete()
    {
        return self::getDataService()->delete($this);
    }

}
