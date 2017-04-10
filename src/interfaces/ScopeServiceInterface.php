<?php
/**
 * ScopeServiceInterface.php
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
 * This is the scope service interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\interfaces
 * @since 1.0.0
 */
interface ScopeServiceInterface
{
    /**
     * Save or update scope depending on isNewRecord flag
     * @param ScopeModelInterface $scope
     * @param null|array $attributes attributes to save
     * @return boolean
     * @throws \Exception
     */
    public function save(ScopeModelInterface $scope, $attributes);

    /**
     * @param string $key
     * @return ScopeModelInterface|null
     */
    public function findOne($key);

    /**
     * @param ScopeModelInterface $scope
     * @return boolean
     */
    public function delete(ScopeModelInterface $scope);

    /**
     * @return array list of scope IDs
     * @since 1.0.0
     */
    public function findAvailableScopeIds();

    /**
     * @param string $clientId
     * @return array list of default scope IDs for selected client Id
     * @since 1.0.0
     */
    public function findDefaultScopeIds($clientId = null);

}
