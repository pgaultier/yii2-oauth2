<?php
/**
 * TypeConverter.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\traits\mySql
 */

namespace sweelix\oauth2\server\traits\mySql;

use yii\base\InvalidArgumentException;
use yii\helpers\Json;

/**
 * This trait convert data from the db to match original types
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package modules\v1\traits\mySql
 * @since 1.0.0
 */
trait TypeConverter
{
    /**
     * @var array attributes definitions declared in model
     */
    private $attributesDefinitions;

    /**
     * @param array $attributesDefinitions attributes definitions declared in the model
     * @since 1.0.0
     */
    public function setAttributesDefinitions($attributesDefinitions)
    {
        $this->attributesDefinitions = $attributesDefinitions;
    }

    /**
     * @param string $key attribute name
     * @param mixed $value attribute value
     * @return mixed type compliant with mysql
     * @since 1.0.0
     */
    public function convertToDatabase($key, $value)
    {
        $bypassTypes = ['string'];
        if ((isset($this->attributesDefinitions[$key]) === true)
            && (in_array($this->attributesDefinitions[$key], $bypassTypes) === false)
        ) {
            switch($this->attributesDefinitions[$key]) {
                case 'bool':
                case 'boolean':
                    $value = $value ? 1 : 0;
                    break;
                case 'int':
                case 'integer':
                    break;
                case 'array':
                    if (is_array($value) === false) {
                        $value = [];
                    }
                    $value = Json::encode($value);
                    break;
                case 'date':
                    $value = date('Y-m-d H:i:s', (int)$value);
                    break;
                case 'real':
                case 'double':
                case 'float':
                    break;
            }

        }
        return $value;
    }

    /**
     * @param string $key attribute name
     * @param string $value attribute value
     * @return mixed value in original datatype
     * @since 1.0.0
     */
    public function convertToModel($key, $value)
    {
        $bypassTypes = ['string'];
        if ((isset($this->attributesDefinitions[$key]) === true)
            && (in_array($this->attributesDefinitions[$key], $bypassTypes) === false)
        ) {
            switch ($this->attributesDefinitions[$key]) {
                case 'bool':
                case 'boolean':
                    $value = (bool) $value;
                    break;
                case 'int':
                case 'integer':
                    $value = (int) $value;
                    break;
                case 'array':
                    if (is_array($value) === false) {
                        try {
                            $value = Json::decode($value);
                        } catch (InvalidArgumentException $e) {
                            $value = [];
                        }
                    }
                    break;
                case 'date':
                    $value = strtotime($value);
                    break;
                case 'real':
                case 'double':
                case 'float':
                    $value = (float) $value;
                    break;
            }
        }
        return $value;
    }
}
