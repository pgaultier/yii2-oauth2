<?php
/**
 * ScopeModelInterface.php
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
 * @property bool $isDefault
 * @property string $definition
 */
interface ScopeModelInterface extends BaseModelInterface
{
    /**
     * Find one scope by its key
     *
     * @param string $id
     * @return ScopeModelInterface|null
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
     * @return array list of scope IDs
     * @since 1.0.0
     */
    public static function findAvailableScopeIds();

    /**
     * @param string $clientId
     * @return array default scope for selected client Id
     * @since 1.0.0
     */
    public static function findDefaultScopeIds($clientId = null);
}
