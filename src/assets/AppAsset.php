<?php
/**
 * AppAsset.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @package sweelix\oauth2\server\assets
 */

namespace sweelix\oauth2\server\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * App Assets
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package sweelix\oauth2\server\assets
 * @since XXX
 */
class AppAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@sweelix/oauth2/server/assets/app';

    /**
     * @inheritdoc
     */
    public $css = [
        'css/main.css',
        '//fonts.googleapis.com/css?family=Raleway:400,200',
    ];

    /**
     * @inheritdoc
     */
    public $jsOptions = ['position' => View::POS_HEAD];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\bootstrap\BootstrapAsset'
    ];
}
