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
 * @package sweelix\oauth2\server\services\mySql
 */

namespace sweelix\oauth2\server\services\mySql;

use sweelix\oauth2\server\interfaces\BaseModelInterface;
use sweelix\oauth2\server\Module;
use sweelix\oauth2\server\traits\mySql\TypeConverter;
use yii\base\BaseObject;
use yii\di\Instance;
use yii\helpers\Json;
use yii\db\Connection;

/**
 * This is the base service for mySql
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package modules\v1\services\mySql
 * @since 1.0.0
 */
class BaseService extends BaseObject
{
    use TypeConverter;

    /**
     * @var string namespace used for key generation
     */
    public $namespace = '';

    /**
     * @var Connection|array|string the MySql DB connection object or the application component ID of the DB connection.
     */
    protected $db;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure(Module::getInstance()->db, Connection::class);
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
