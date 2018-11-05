<?php
/**
 * ScopeController.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\commands
 */

namespace sweelix\oauth2\server\commands;

use yii\console\Controller;
use Yii;
use yii\console\ExitCode;

/**
 * Manage oauth scopes
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\commands
 * @since 1.0.0
 */
class ScopeController extends Controller
{

    public $isDefault = false;
    public $definition;

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return [
            // 'id',
            'isDefault',
            'definition',
        ];
    }

    /**
     * Create new Oauth scope
     * @param $id
     * @return int
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\UnknownClassException
     * @since 1.0.0
     */
    public function actionCreate($id)
    {
        $scope = Yii::createObject('sweelix\oauth2\server\interfaces\ScopeModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\ScopeModelInterface $scope */
        $scope->id = $id;
        $scope->isDefault = (bool)$this->isDefault;
        $scope->definition = $this->definition;
        if ($scope->save() === true) {
            $this->stdout('Scope created :' . "\n");
            $this->stdout(' - id: ' . $scope->id . "\n");
            $this->stdout(' - isDefault: ' . ($scope->isDefault ? 'Yes' : 'No') . "\n");
            $this->stdout(' - definition: ' . $scope->definition . "\n");
            return ExitCode::OK;
        } else {
            $this->stdout('Scope cannot be created.' . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Update Oauth Scope
     * @param $id
     * @return int
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\UnknownClassException
     */
    public function actionUpdate($id)
    {
        $scope = Yii::createObject('sweelix\oauth2\server\interfaces\ScopeModelInterface');
        $scopeClass = get_class($scope);
        /* @var \sweelix\oauth2\server\interfaces\ScopeModelInterface $scope */
        $scope = $scopeClass::findOne($id);
        if ($scope !== null) {
            $scope->isDefault = $this->isDefault;
            $scope->definition = $this->definition;
            if ($scope->save() === true) {
                $this->stdout('Scope updated :' . "\n");
                $this->stdout(' - id: ' . $scope->id . "\n");
                $this->stdout(' - isDefault: ' . ($scope->isDefault ? 'Yes' : 'No') . "\n");
                $this->stdout(' - definition: ' . $scope->definition . "\n");
                return ExitCode::OK;
            } else {
                $this->stdout('Scope ' . $id . ' cannot be updated' . "\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }
        } else {
            $this->stdout('Scope ' . $id . ' does not exist' . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Delete Oauth Scope
     * @param $id
     * @return int
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\UnknownClassException
     */
    public function actionDelete($id)
    {
        $scope = Yii::createObject('sweelix\oauth2\server\interfaces\ScopeModelInterface');
        $scopeClass = get_class($scope);
        /* @var \sweelix\oauth2\server\interfaces\ScopeModelInterface $scope */
        $scope = $scopeClass::findOne($id);
        if ($scope !== null) {
            if ($scope->delete() === true) {
                $this->stdout('Scope ' . $id . ' deleted' . "\n");
                return ExitCode::OK;
            } else {
                $this->stdout('Scope ' . $id . ' cannot be deleted' . "\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }
        } else {
            $this->stdout('Scope ' . $id . ' does not exist' . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}