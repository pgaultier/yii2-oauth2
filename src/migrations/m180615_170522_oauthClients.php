<?php
/**
 * m180615_170522_oauthClients.php
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
 * Class m180615_170522_oauthClients
 *
 * @author Maxime Deschamps <mdeschamps@ibitux.com>
 * @copyright 2010-2018 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package src\migrations
 * @since XXX
 */
class m180615_170522_oauthClients extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%oauthClients}}', [
            'id' => $this->string(255),
            'secret' => $this->string(255),
            'isPublic' => $this->boolean(),
            'redirectUri' => $this->string(255),
            'userId' => $this->string(255),
            'name' => $this->string(255),
            'dateCreated' => $this->datetime(),
            'dateUpdated' => $this->datetime(),
            'dateDeleted' => $this->dateTime(),
            'PRIMARY KEY(id)',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%oauthClients}}');
        return true;
    }

}
