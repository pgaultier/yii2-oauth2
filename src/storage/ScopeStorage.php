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
 */

namespace sweelix\oauth2\server\storage;

use OAuth2\Storage\ScopeInterface;
use sweelix\oauth2\server\models\Scope;

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