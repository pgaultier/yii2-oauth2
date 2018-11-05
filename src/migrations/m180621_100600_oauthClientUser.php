<?php
/**
 * m180621_100600_oauthClientUser.php
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
 * Class m180621_100600_oauthClientUser
 *
 * @author Maxime Deschamps <mdeschamps@ibitux.com>
 * @copyright 2010-2018 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package src\migrations
 * @since XXX
 */
class m180621_100600_oauthClientUser extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%oauthClientUser}}', [
            'clientId' => $this->string(255),
            'userId' => $this->string(255),
            'PRIMARY KEY(clientId, userId)',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addForeignKey(
            'client_user_client_id_fk',
            '{{%oauthClientUser}}',
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
        $this->dropForeignKey('client_user_client_id_fk', '{{%oauthClientUser}}');
        $this->dropTable('{{%oauthClientUser}}');
        return true;
    }

}
