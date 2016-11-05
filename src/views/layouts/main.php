<?php
/**
 * main.php
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package sweelix\oauth2\server\views\layouts
 *
 */
use yii\helpers\Html;

$this->beginPage(); ?>
    <!DOCTYPE html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?php echo Html::encode($this->title); ?></title>

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <?php $this->head(); ?>
    </head>
    <body>
        <?php $this->beginBody(); ?>
            <?php echo $content;?>
        <?php $this->endBody(); ?>
    </body>

</html>
<?php $this->endPage();
