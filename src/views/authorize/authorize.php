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
<?php /* if(empty($requestedScopes) === false) : ?>
    <div>
        Liste des scopes demandés :
        <ul>
            <?php foreach($requestedScopes as $scope): ?>
                <li><?php echo $scope['id']. ' ' . (empty($scope['description']) ? '' : $scope['description']); ?> </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif;*/ ?>

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
