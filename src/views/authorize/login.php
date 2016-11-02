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
?>

<?php echo Html::beginForm(); ?>

    <fieldset>
        <legend>Autoriser l'application XXX</legend>
        <div>
            Liste des scopes demandés :
            <ul>
                <?php foreach($requestedScopes as $scope): ?>
                    <li><?php echo $scope['id']. ' ' . (empty($scope['description']) ? '' : $scope['description']); ?> </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <label>Login</label>
        <?php echo Html::activeTextInput($user, 'username'); ?><br/>
        <label>Password</label>
        <?php echo Html::activePasswordInput($user, 'password'); ?><br/>
        <button type="submit">Autoriser</button>
    </fieldset>
<?php echo Html::endForm(); ?>
