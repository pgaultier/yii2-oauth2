<?php
/**
 * m180619_095000_oauthGrantTypes.php
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

use yii\db\Expression;
use yii\db\Migration;

/**
 * Class m180619_095000_oauthGrantTypes
 *
 * @author Maxime Deschamps <mdeschamps@ibitux.com>
 * @copyright 2010-2018 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package src\migrations
 * @since XXX
 */
class m180619_095000_oauthGrantTypes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%oauthGrantTypes}}', [
            'id' => $this->string(255),
            'dateCreated' => $this->datetime(),
            'dateUpdated' => $this->datetime(),
            'dateDeleted' => $this->dateTime(),
            'PRIMARY KEY(id)',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->insert('{{%oauthGrantTypes}}', [
            'id' => 'password',
            'dateCreated' => new Expression('NOW()'),
            'dateUpdated' => new Expression('NOW()'),
        ]);
        $this->insert('{{%oauthGrantTypes}}', [
            'id' => 'client_credentials',
            'dateCreated' => new Expression('NOW()'),
            'dateUpdated' => new Expression('NOW()'),
        ]);
        $this->insert('{{%oauthGrantTypes}}', [
            'id' => 'refresh_token',
            'dateCreated' => new Expression('NOW()'),
            'dateUpdated' => new Expression('NOW()'),
        ]);
        $this->insert('{{%oauthGrantTypes}}', [
            'id' => 'authorization_code',
            'dateCreated' => new Expression('NOW()'),
            'dateUpdated' => new Expression('NOW()'),
        ]);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%oauthGrantTypes}}');
        return true;
    }

}
