<?php
/**
 * m180618_131600_oauthJwts.php
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
 * Class m180618_131600_oauthJwts
 *
 * @author Maxime Deschamps <mdeschamps@ibitux.com>
 * @copyright 2010-2018 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package src\migrations
 * @since XXX
 */
class m180618_131600_oauthJwts extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%oauthJwts}}', [
            'id' => $this->string(255),
            'clientId' => $this->string(255),
            'subject' => $this->string(255),
            'publicKey' => $this->text(),
            'dateCreated' => $this->datetime(),
            'dateUpdated' => $this->datetime(),
            'dateDeleted' => $this->dateTime(),
            'PRIMARY KEY(id)',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addForeignKey(
            'jwts_clients_id_fk',
            '{{%oauthJwts}}',
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
        $this->dropForeignKey('jwts_clients_id_fk', '{{%oauthJwts}}');
        $this->dropTable('{{%oauthJwts}}');
        return true;
    }

}
