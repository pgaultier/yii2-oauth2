<?php
/**
 * BearerService.php
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

use sweelix\oauth2\server\models\BaseModel;
use sweelix\oauth2\server\traits\redis\TypeConverter;
use yii\base\Object;
use yii\helpers\Json;
use yii\redis\Connection;
use Yii;

/**
 * This is the base service for redis
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package modules\v1\services\redis
 * @since XXX
 */
class BaseService extends Object
{
    use TypeConverter;

    /**
     * @var string namespace used for key generation
     */
    public $namespace = '';

    /**
     * @var Connection|array|string the Redis DB connection object or the application component ID of the DB connection.
     */
    protected $db;

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        $this->db = Yii::$app->redis;
        parent::__construct($config);
    }

    /**
     * Compute etag based on model attributes
     * @param BaseModel $model
     * @return string
     * @since XXX
     */
    protected function computeEtag(BaseModel $model)
    {
        return $this->encodeAttributes($model->attributes);
    }

    /**
     * Encode attributes array
     *
     * @param array $attributes
     *
     * @return string
     * @since  XXX
     */
    protected function encodeAttributes(Array $attributes)
    {
        $data = Json::encode($attributes);
        $etag = '"' . rtrim(base64_encode(sha1($data, true)), '=') . '"';
        return $etag;
    }

}
