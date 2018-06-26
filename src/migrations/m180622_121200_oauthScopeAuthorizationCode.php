<?php
/**
 * m180622_121200_oauthScopeAuthorizationCode.php
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
 * Class m180622_121200_oauthScopeAuthorizationCode
 *
 * @author Maxime Deschamps <mdeschamps@ibitux.com>
 * @copyright 2010-2018 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package src\migrations
 * @since XXX
 */
class m180622_121200_oauthScopeAuthorizationCode extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%oauthScopeAuthorizationCode}}', [
            'scopeId' => $this->string(255),
            'authorizationCodeId' => $this->string(255),
            'PRIMARY KEY(scopeId, authorizationCodeId)',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addForeignKey(
            'scope_authorizationCode_scopes_id_fk',
            '{{%oauthScopeAuthorizationCode}}',
            'scopeId',
            '{{%oauthScopes}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'scope_authorizationCode_authorizationCodes_id_fk',
            '{{%oauthScopeAuthorizationCode}}',
            'authorizationCodeId',
            '{{%oauthAuthorizationCodes}}',
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
        $this->dropForeignKey('scope_authorizationCode_authorizationCodes_id_fk', '{{%oauthScopeAuthorizationCode}}');
        $this->dropForeignKey('scope_authorizationCode_scopes_id_fk', '{{%oauthScopeAuthorizationCode}}');
        $this->dropTable('{{%oauthScopeAuthorizationCode}}');
        return true;
    }

}
