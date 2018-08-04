<?php
/**
 * m180618_143800_oauthRefreshTokens.php
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
 * Class m180618_143800_oauthRefreshTokens
 *
 * @author Maxime Deschamps <mdeschamps@ibitux.com>
 * @copyright 2010-2018 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package src\migrations
 * @since XXX
 */
class m180618_144400_oauthScopeAccessToken extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%oauthScopeAccessToken}}', [
            'scopeId' => $this->string(255),
            'accessTokenId' => $this->string(750),
            'PRIMARY KEY(scopeId, accessTokenId)',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addForeignKey(
            'scope_accessToken_scopes_id_fk',
            '{{%oauthScopeAccessToken}}',
            'scopeId',
            '{{%oauthScopes}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'scope_accessToken_accessTokens_id_fk',
            '{{%oauthScopeAccessToken}}',
            'accessTokenId',
            '{{%oauthAccessTokens}}',
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
        $this->dropForeignKey('scope_accessToken_accessTokens_id_fk', '{{%oauthScopeAccessToken}}');
        $this->dropForeignKey('scope_accessToken_scopes_id_fk', '{{%oauthScopeAccessToken}}');
        $this->dropTable('{{%oauthScopeAccessToken}}');
        return true;
    }

}
