<?php
/**
 * m180618_134300_oauthAuthorizationCodes.php
 * PHP version 5.6+
 *
 * Create applicants
 *
 * @author Maxime Deschamps <mdeschamps@ibitux.com>
 * @copyright 2010-2018 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package src\migrations
 */

use yii\db\Migration;

/**
 * Class m180618_134300_oauthAuthorizationCodes
 *
 * @author Maxime Deschamps <mdeschamps@ibitux.com>
 * @copyright 2010-2018 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package src\migrations
 * @since XXX
 */
class m180618_134300_oauthAuthorizationCodes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%oauthAuthorizationCodes}}', [
            'id' => $this->string(255),
            'clientId' => $this->string(255),
            'userId' => $this->string(255),
            'redirectUri' => $this->string(255),
            'expiry' => $this->dateTime(),
            'tokenId' => $this->string(255),
            'dateCreated' => $this->datetime(),
            'dateUpdated' => $this->datetime(),
            'dateDeleted' => $this->dateTime(),
            'PRIMARY KEY(id)',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addForeignKey(
            'authorizationCodes_clients_id_fk',
            '{{%oauthAuthorizationCodes}}',
            'clientId',
            '{{%oauthClients}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        $this->addForeignKey(
            'authorizationCodes_accessTokens_id_fk',
            '{{%oauthAuthorizationCodes}}',
            'tokenId',
            '{{%oauthAccessTokens}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('authorizationCodes_accessTokens_id_fk', '{{%oauthAuthorizationCodes}}');
        $this->dropForeignKey('authorizationCodes_clients_id_fk', '{{%oauthAuthorizationCodes}}');
        $this->dropTable('{{%oauthAuthorizationCodes}}');
        return true;
    }

}
