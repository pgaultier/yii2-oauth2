<?php
/**
 * m180619_095100_oauthClientGrantType.php
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
 * Class m180619_095100_oauthClientGrantType
 *
 * @author Maxime Deschamps <mdeschamps@ibitux.com>
 * @copyright 2010-2018 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package src\migrations
 * @since XXX
 */
class m180619_095100_oauthClientGrantType extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%oauthClientGrantType}}', [
            'clientId' => $this->string(255),
            'grantTypeId' => $this->string(255),
            'PRIMARY KEY(clientId, grantTypeId)',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addForeignKey(
            'client_grantType_client_id_fk',
            '{{%oauthClientGrantType}}',
            'clientId',
            '{{%oauthClients}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'client_grantType_grantType_id_fk',
            '{{%oauthClientGrantType}}',
            'grantTypeId',
            '{{%oauthGrantTypes}}',
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
        $this->dropForeignKey('client_grantType_grantType_id_fk', '{{%oauthClientGrantType}}');
        $this->dropForeignKey('client_grantType_client_id_fk', '{{%oauthClientGrantType}}');
        $this->dropTable('{{%oauthClientGrantType}}');
        return true;
    }

}
