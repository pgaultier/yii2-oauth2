<?php
/**
 * m180618_132800_oauthAccessTokens.php
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
 * Class m180618_132800_oauth_accessTokens
 *
 * @author Maxime Deschamps <mdeschamps@ibitux.com>
 * @copyright 2010-2018 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package src\migrations
 * @since XXX
 */
class m180618_132800_oauthAccessTokens extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%oauthAccessTokens}}', [
            'id' => $this->string(255),
            'clientId' => $this->string(255),
            'userId' => $this->string(255),
            'expiry' => $this->dateTime(),
            'dateCreated' => $this->datetime(),
            'dateUpdated' => $this->datetime(),
            'dateDeleted' => $this->dateTime(),
            'PRIMARY KEY(id)',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addForeignKey(
            'accessTokens_clients_id_fk',
            '{{%oauthAccessTokens}}',
            'clientId',
            '{{%oauthClients}}',
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
        $this->dropForeignKey('accessTokens_clients_id_fk', '{{%oauthAccessTokens}}');
        $this->dropTable('{{%oauthAccessTokens}}');
        return true;
    }

}
