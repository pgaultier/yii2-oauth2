<?php
/**
 * authorize.php
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
 * @var array $requestedScopes
 * @var \sweelix\oauth2\server\interfaces\ClientModelInterface $client
 */
use yii\helpers\Html;
use sweelix\oauth2\server\assets\AppAsset;

$baseAppUrl = AppAsset::register($this)->baseUrl;

?>


<div class="container">
    <div class="row ">
        <div class="col-md-push-3 col-md-6 col-xs-12 login_box" align="center">
            <div class="outter">
                <?php echo Html::img($baseAppUrl.'/img/logo.png', ['class' => 'image-circle']); ?>
            </div>
            <h1><?php echo $client->name ?></h1>
            <span>requests access to Sweelix</span>
        </div>
    </div>
    <div class="row">
        <div class="col-md-push-3 col-md-6 col-xs-12 white-panel">
            <?php echo Html::beginForm(); ?>
            <?php if(empty($requestedScopes) === false) : ?>
            <ul class="list-group">
                <?php foreach($requestedScopes as $scope): ?>
                <li class="list-group-item">
                    <h4 class="list-group-item-heading"><?php echo $scope['id']; ?></h4>
                    <p class="list-group-item-text">
                        <?php echo (empty($scope['description']) ? '' : $scope['description']); ?>
                    </p>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-6 col-xs-6">
                    <button class="btn btn-danger btn-block btn-lg" type="submit" name="decline">DECLINE</button>
                </div>
                <div class="col-md-6 col-xs-6">
                    <button class="btn btn-success btn-block btn-lg" type="submit" name="accept">AUTHORIZE</button>
                </div>
            </div>

            <?php echo Html::endForm(); ?>
        </div>



    </div>
</div>



<?php /*
<div class="login">
    <div class="login-header">
        <div class="grid-container">
            <div class="grid-50">
                <div class="login-logo">
                    <a href="#">
                        <?php
                        echo Html::img($baseAppUrl.'/img/logo.png', '', ['height' => 85]);
                        ?>
                    </a>
                </div>
            </div>
            <div class="grid-50">
                <h1 class="login-title text-uppercase text-right">Autorisation</h1>
            </div>
        </div>
    </div>
    <!-- login-header -->

    <div class="login-body">
        <div class="login-form">
            <?php echo Html::beginForm(); ?>
            <div class="login-form-group">
                <?php echo Html::label('Autoriser '.$client->name.' à accéder à vos données ?', null, [
                    'class' => 'login-form-help'
                ])?>
            </div>
            <div class="login-form-group form-group">
                <ul>
                    <li>aaa</li>
                </ul>
            </div>
            <div class="login-form-help">En cas de doute <a href="#">cliquer ici</a></div>
            <div class="login-form-button">
                <button type="submit" name="accept" class="button button-large button-icon-left button-blue text-uppercase">
                    <i class="icon icon-triangle-right"></i>
                    <span class="text">Accepter</span>
                </button>
            </div>
            <div class="login-form-button">
                <button type="submit" name="decline" class="button button-large button-icon-left button-blue text-uppercase">
                    <i class="icon icon-triangle-right"></i>
                    <span class="text">Refuser</span>
                </button>
            </div>
            <?php echo Html::endForm();?>
        </div>
    </div>
    <!-- login-body -->

</div>
*/ ?>
