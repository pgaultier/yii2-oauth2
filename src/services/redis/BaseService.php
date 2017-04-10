<?php
/**
 * BearerService.php
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

use sweelix\oauth2\server\interfaces\BaseModelInterface;
use sweelix\oauth2\server\models\BaseModel;
use sweelix\oauth2\server\Module;
use sweelix\oauth2\server\traits\redis\TypeConverter;
use yii\base\Object;
use yii\di\Instance;
use yii\helpers\Json;
use yii\redis\Connection;
use Yii;

/**
 * This is the base service for redis
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package modules\v1\services\redis
 * @since 1.0.0
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
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure(Module::getInstance()->db, Connection::className());
    }

    /**
     * Compute etag based on model attributes
     * @param BaseModelInterface $model
     * @return string
     * @since 1.0.0
     */
    protected function computeEtag(BaseModelInterface $model)
    {
        return $this->encodeAttributes($model->attributes);
    }

    /**
     * Encode attributes array
     *
     * @param array $attributes
     *
     * @return string
     * @since  1.0.0
     */
    protected function encodeAttributes(Array $attributes)
    {
        $data = Json::encode($attributes);
        $etag = '"' . rtrim(base64_encode(sha1($data, true)), '=') . '"';
        return $etag;
    }

}
