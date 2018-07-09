<?php
/**
 * m180618_113800_oauthJtis.php
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
 * Class m180618_113800_oauthJtis
 *
 * @author Maxime Deschamps <mdeschamps@ibitux.com>
 * @copyright 2010-2018 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package src\migrations
 * @since XXX
 */
class m180618_113800_oauthJtis extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%oauthJtis}}', [
            'id' => $this->string(255),
            'clientId' => $this->string(255),
            'subject' => $this->string(255),
            'audience' => $this->string(255),
            'expires' => $this->dateTime(),
            'jti' => $this->text(),
            'dateCreated' => $this->datetime(),
            'dateUpdated' => $this->datetime(),
            'dateDeleted' => $this->dateTime(),
            'PRIMARY KEY(id)',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addForeignKey(
            'jtis_clients_id_fk',
            '{{%oauthJtis}}',
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
        $this->dropForeignKey('jtis_clients_id_fk', '{{%oauthJtis}}');
        $this->dropTable('{{%oauthJtis}}');
        return true;
    }

}
