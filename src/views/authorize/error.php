<?php
/**
 * error.php
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
 * @var string $type error type
 * @var string $description error description
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
            <h1>Bad Request</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-push-3 col-md-6 col-sm-push-2 col-sm-8 col-xs-12 white-panel">
            <div class="alert" role="alert">
                <h4 class="alert-heading"><?php echo ($type ? : 'Unkown error'); ?></h4>
                <p><?php echo ($description ? : 'Please check your request'); ?></p>
            </div>
        </div>
    </div>
</div>
