<?php
/**
 * authorize.php
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
 * @var \sweelix\oauth2\server\interfaces\ScopeModelInterface[] $requestedScopes
 * @var \sweelix\oauth2\server\interfaces\ClientModelInterface $client
 */
use yii\helpers\Html;
use sweelix\oauth2\server\assets\AppAsset;

$baseAppUrl = AppAsset::register($this)->baseUrl;

?>


<div class="container">
    <div class="row ">
        <div class="col-md-push-3 col-md-6 col-sm-push-2 col-sm-8 col-xs-12 orange-panel">
            <div class="outter">
                <?php echo Html::img($baseAppUrl.'/img/logo.png', ['class' => 'image-circle']); ?>
            </div>
            <h1><?php echo $client->name ?></h1>
            <span>requests access to Sweelix</span>
        </div>
    </div>
    <div class="row">
        <div class="col-md-push-3 col-md-6 col-sm-push-2 col-sm-8 col-xs-12 white-panel">
            <?php echo Html::beginForm(); ?>
            <?php if(empty($requestedScopes) === false) : ?>
            <ul class="list-group">
                <?php foreach($requestedScopes as $scope): ?>
                <li class="list-group-item">
                    <h4 class="list-group-item-heading"><?php echo $scope->id; ?></h4>
                    <p class="list-group-item-text">
                        <?php echo $scope->definition; ?>
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
