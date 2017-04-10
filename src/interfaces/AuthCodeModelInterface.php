<?php
/**
 * AuthCodeModelInterface.php
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
 * This is the auth code model interface
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
 * @property string $clientId
 * @property string $userId
 * @property string $redirectUri
 * @property string $expiry
 * @property array $scopes
 * @property string $tokenId
 */
interface AuthCodeModelInterface extends BaseModelInterface
{
    /**
     * Find one auth code by its key
     *
     * @param string $id
     * @return AuthCodeModelInterface|null
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
}
