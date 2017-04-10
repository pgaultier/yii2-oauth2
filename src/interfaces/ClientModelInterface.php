<?php
/**
 * ClientModelInterface.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 */

namespace sweelix\oauth2\server\interfaces;

/**
 * This is the client model interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since 1.0.0
 *
 * @property string $id
 * @property string $secret
 * @property string|array $redirectUri
 * @property array $grantTypes
 * @property string $userId
 * @property array $scopes
 * @property string $name
 * @property bool $isPublic
 */
interface ClientModelInterface extends BaseModelInterface
{
    /**
     * Find one client by its key
     *
     * @param string $id
     * @return ClientModelInterface|null
     * @since 1.0.0
     * @throws \yii\base\UnknownClassException
     */
    public static function findOne($id);

    /**
     * @param bool $runValidation
     * @param null $attributes
     * @return bool
     * @since 1.0.0
     * @throws \yii\base\UnknownClassException
     */
    public function save($runValidation = true, $attributes = null);

    /**
     * @return bool
     * @since 1.0.0
     * @throws \yii\base\UnknownClassException
     */
    public function delete();

    /**
     * @param $userId
     * @return bool
     * @since 1.0.0
     */
    public function hasUser($userId);

    /**
     * @param $userId
     * @return bool
     * @since 1.0.0
     */
    public function addUser($userId);

    /**
     * @param $userId
     * @return bool
     * @since 1.0.0
     */
    public function removeUser($userId);

    /**
     * @param $userId
     * @return ClientModelInterface[]
     * @since XXX
     */
    public static function findAllByUserId($userId);
}
