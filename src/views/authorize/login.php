<?php
/**
 * login.php
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package sweelix\oauth2\server\views\authorize
 *
 * @var \sweelix\oauth2\server\forms\User $user
 */
use yii\helpers\Html;
use sweelix\oauth2\server\assets\AppAsset;

$baseAppUrl = AppAsset::register($this)->baseUrl;
$passwordClass = ['login-form-textbox'];
if ($user->hasErrors('userPassword')) {
    $passwordClass[] = 'error';
}

$emailClass = ['login-form-textbox'];
if ($user->hasErrors('userEmail')) {
    $emailClass[] = 'error';
}

?>

<div class="login">
    <div class="login-header">
        <div class="grid-container">
            <div class="grid-60">
                <div class="login-logo">
                    <a href="#">
                        <?php
                        echo Html::img($baseAppUrl.'/img/logo.png', '', ['height' => 85]);
                        ?>
                    </a>
                </div>
            </div>
            <div class="grid-40">
                <h1 class="login-title text-uppercase text-right">Sign in</h1>
            </div>
        </div>
    </div>
    <!-- login-header -->
    <div class="login-body">
        <div class="login-form">
            <?php echo Html::beginForm(); ?>
            <div class="login-form-group">
                <?php echo Html::activeTextInput($user, 'username', [
                    'class' => implode(' ', $emailClass),
                    'placeholder' => 'Email'
                ]); ?>
            </div>
            <div class="login-form-group">
                <?php echo Html::activePasswordInput($user, 'password', [
                    'class' => implode(' ', $passwordClass),
                    'placeholder' => 'Password'
                ]); ?>
            </div>
            <!-- div class="login-form-help">
                <span>
                    Forgot your password ? <a href="#">Click here</a>
                </span>
            </div>
            <div class="login-form-group">
                <span>
                    No ID ?
                    <?php // echo Html::a('Register', ['oauth/register', 'client_id' => $clientId]); ?>
                </span>
            </div -->

            <div class="login-form-button">
                <button type="submit" class="button button-large button-icon-left button-blue text-uppercase">
                    <i class="icon icon-triangle-right"></i>
                    <span class="text">Connect</span>
                </button>
            </div>
            <?php echo Html::endForm();?>
        </div>
    </div>
    <!-- login-body -->

</div>
