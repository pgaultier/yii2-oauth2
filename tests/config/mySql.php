<?php
/**
 * mySql.php
 *
 * PHP version 5.6+
 *
 * @author Philippe Gaultier <pgaultier@sweelix.net>
 * @copyright 2010-2016 Philippe Gaultier
 * @license http://www.sweelix.net/license license
 * @version XXX
 * @link http://www.sweelix.net
 * @since XXX
 */

$isGitlab = getenv('GITLAB_CI');
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=oauth2',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];