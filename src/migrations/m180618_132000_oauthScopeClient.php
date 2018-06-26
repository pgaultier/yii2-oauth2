<?php
/**
 * m180618_132000_oauth_scopeClient.php
 *
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
 * Class m180618_132000_oauth_scopeClient
 *
 * @author Maxime Deschamps <mdeschamps@ibitux.com>
 * @copyright 2010-2018 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package src\migrations
 * @since XXX
 */
class m180618_132000_oauthScopeClient extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%oauthScopeClient}}', [
            'scopeId' => $this->string(255),
            'clientId' => $this->string(255),
            'PRIMARY KEY(scopeId, clientId)',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addForeignKey(
            'scope_client_scopes_id_fk',
            '{{%oauthScopeClient}}',
            'scopeId',
            '{{%oauthScopes}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'scope_client_clients_id_fk',
            '{{%oauthScopeClient}}',
            'clientId',
            '{{%oauthClients}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('scope_client_clients_id_fk', '{{%oauthScopeClient}}');
        $this->dropForeignKey('scope_client_scopes_id_fk', '{{%oauthScopeClient}}');
        $this->dropTable('{{%oauthScopeClient}}');
        return true;
    }

}
