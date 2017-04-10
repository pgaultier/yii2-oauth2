<?php
/**
 * login.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2017 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version 1.2.0
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\views\authorize
 *
 * @var \sweelix\oauth2\server\forms\User $user
 */
use yii\helpers\Html;
use sweelix\oauth2\server\assets\AppAsset;

$baseAppUrl = AppAsset::register($this)->baseUrl;
$passwordClass = ['form-control'];
if ($user->hasErrors('password')) {
    $passwordClass[] = 'error';
}

$emailClass = ['form-control'];
if ($user->hasErrors('username') || $user->hasErrors('password')) {
    $emailClass[] = 'error';
}

?>


<div class="container">
    <div class="row ">
        <div class="col-md-push-3 col-md-6 col-sm-push-2 col-sm-8 col-xs-12 orange-panel">
            <div class="outter">
                <?php echo Html::img($baseAppUrl.'/img/logo.png', ['class' => 'image-circle']); ?>
            </div>
            <h1>Sweelix</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-push-3 col-md-6 col-sm-push-2 col-sm-8 col-xs-12 white-panel">
            <?php echo Html::beginForm('', 'post', ['novalidate' => 'novalidate']); ?>

            <div class="control">
                <div class="label">Username</div>
                <?php echo Html::activeTextInput($user, 'username', [
                    'class' => implode(' ', $emailClass),
                    'placeholder' => 'Username',
                    'required' => 'required',
                ]); ?>

            </div>

            <div class="control">
                <div class="label">Password</div>
                <?php echo Html::activePasswordInput($user, 'password', [
                    'class' => implode(' ', $passwordClass),
                    'placeholder' => 'Password'
                ]); ?>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button class="btn btn-success btn-block btn-lg" type="submit">LOGIN</button>
                </div>

            </div>

            <?php echo Html::endForm(); ?>
        </div>



    </div>
</div>
