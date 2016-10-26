<?php
/**
 * ScopeStorage.php
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package sweelix\oauth2\server\models
 */

namespace sweelix\oauth2\server\storage;

use OAuth2\Storage\ScopeInterface;
use sweelix\oauth2\server\models\Scope;

/**
 * This is the scope service interface
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\models
 * @since XXX
 */
class ScopeStorage implements ScopeInterface
{
    /**
     * @inheritdoc
     */
    public function scopeExists($scope)
    {
        $availableScopes = Scope::findAvailableScopeIds();
        $requestedScopes = explode(' ', $scope);
        $missingScopes = array_diff($requestedScopes, $availableScopes);
        return empty($missingScopes);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultScope($client_id = null)
    {
        $availableDefaultScopes = Scope::findDefaultScopeIds($client_id);
        $scope = implode(' ', $availableDefaultScopes);
        if (empty($scope) === true) {
            $scope = null;
        }
        return $scope;
    }
}