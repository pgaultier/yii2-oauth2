<?php
/**
 * User.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\models
 * @since 1.0.0
 */

namespace sweelix\oauth2\server\web;

use sweelix\oauth2\server\interfaces\UserModelInterface;
use yii\web\User as BaseUser;

/**
 * This user model extends yii\web\User to handle scope authorization in can() assertion
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\models
 * @since 1.0.0
 */
class User extends BaseUser
{
    /**
     * @inheritdoc
     */
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        $oauth = true;
        $rbac = true;
        $status = true;
        if (strncmp('oauth2:', $permissionName, 7) === 0) {
            $permissionName = substr($permissionName, 7);
            // check only the scope
            $rbac = false;
        } elseif (strncmp('rbac:', $permissionName, 5) === 0) {
            $permissionName = substr($permissionName, 5);
            // check only rbac
            $oauth = false;
        }

        if ($oauth === true) {
            // Check if scope is authorized
            $scopeCheck = true;
            if (($this->identity instanceof UserModelInterface) && ($this->identity->getRestrictedScopes() !== null)) {
                $scopeCheck = in_array($permissionName, $this->identity->getRestrictedScopes());
            }
            $status = $status && $scopeCheck;
        }

        if ($rbac === true) {
            $regularCheck = parent::can($permissionName, $params, $allowCaching);
            $status = $status && $regularCheck;
        }

        // perform regular check


        return $status;
    }
}
