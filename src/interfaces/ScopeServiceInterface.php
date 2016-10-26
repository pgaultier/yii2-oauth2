<?php
/**
 * ScopeServiceInterface.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 */

namespace sweelix\oauth2\server\interfaces;

use sweelix\oauth2\server\models\Scope;

/**
 * This is the scope service interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since XXX
 */
interface ScopeServiceInterface
{
    /**
     * Save or update scope depending on isNewRecord flag
     * @param Scope $$scope
     * @param null|array $attributes attributes to save
     * @return boolean
     * @throws \Exception
     */
    public function save(Scope $scope, $attributes);

    /**
     * @param string $key
     * @return Scope|null
     */
    public function findOne($key);

    /**
     * @param Scope $scope
     * @return boolean
     */
    public function delete(Scope $scope);

    /**
     * @return array list of scope IDs
     * @since XXX
     */
    public function findAvailableScopeIds();

    /**
     * @param string $clientId
     * @return array list of default scope IDs for selected client Id
     * @since XXX
     */
    public function findDefaultScopeIds($clientId = null);

}
