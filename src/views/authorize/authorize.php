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
        <button type="submit" name="accept">Autoriser</button>
        <button type="submit" name="decline">Refuser</button>
    </fieldset>
<?php echo Html::endForm(); ?>
