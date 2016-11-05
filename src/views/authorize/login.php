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
        <div class="col-md-push-3 col-md-6 col-xs-12 login_box" align="center">
            <div class="outter">
                <?php echo Html::img($baseAppUrl.'/img/logo.png', ['class' => 'image-circle']); ?>
            </div>
            <h1>Sweelix</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-push-3 col-md-6 col-xs-12 white-panel">
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
                <div class="col-md-push-3 col-md-6 col-xs-12">
                    <button class="btn btn-success btn-block btn-lg" type="submit">LOGIN</button>
                </div>

            </div>

            <?php echo Html::endForm(); ?>
        </div>



    </div>
</div>
<?php /*

<div class="main">
    <h1>Authenticate</h1>
    <div class="login-form">
        <?php echo Html::beginForm(); ?>
            <?php echo Html::activeTextInput($user, 'username', [
                'class' => implode(' ', $emailClass),
                'placeholder' => 'Username',
                'required' => 'required',
            ]); ?>

            <?php echo Html::activePasswordInput($user, 'password', [
                'class' => implode(' ', $passwordClass),
                'placeholder' => 'Password'
            ]); ?>

            <button type="submit">
                Connect
            </button>

        <?php echo Html::endForm(); ?>
        <!-- div class="login-text">
            <div class="text-left">
                <p><a href="#"> Forgot Password? </a></p>
            </div>
            <div class="text-right">
                <p><a href="#"> Create New Account</a></p>
            </div>
            <div class="clear"> </div>
        </div -->
    </div>
</div>


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
*/ ?>
