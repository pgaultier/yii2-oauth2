<?php
/**
 * ScopeValidator.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\validators
 */

namespace sweelix\oauth2\server\validators;

use sweelix\oauth2\server\models\Scope;
use yii\validators\Validator;

/**
 * This class validate scopes
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\validators
 * @since 1.0.0
 */
class ScopeValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $selectedScopes = $model->{$attribute};
        if ((is_array($selectedScopes) === true) && (empty($selectedScopes) === false)) {
            $availableScopes = Scope::findAvailableScopeIds();
            $missingScopes = array_diff($selectedScopes, $availableScopes);
            if (empty($missingScopes) === false) {
                //TODO: add internationalization
                $model->addError($attribute, $attribute.' is not a valid scope list.');
            }
        }
    }
}
