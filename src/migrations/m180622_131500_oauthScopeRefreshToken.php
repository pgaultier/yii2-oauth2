<?php
/**
 * m180622_131500_oauthScopeRefreshToken.php
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
 * Class m180622_131500_oauthScopeRefreshToken
 *
 * @author Maxime Deschamps <mdeschamps@ibitux.com>
 * @copyright 2010-2018 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package src\migrations
 * @since XXX
 */
class m180622_131500_oauthScopeRefreshToken extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%oauthScopeRefreshToken}}', [
            'scopeId' => $this->string(255),
            'refreshTokenId' => $this->string(255),
            'PRIMARY KEY(scopeId, refreshTokenId)',
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addForeignKey(
            'scope_refreshToken_scopes_id_fk',
            '{{%oauthScopeRefreshToken}}',
            'scopeId',
            '{{%oauthScopes}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'scope_refreshToken_refreshTokens_id_fk',
            '{{%oauthScopeRefreshToken}}',
            'refreshTokenId',
            '{{%oauthRefreshTokens}}',
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
        $this->dropForeignKey('scope_refreshToken_refreshTokens_id_fk', '{{%oauthScopeRefreshToken}}');
        $this->dropForeignKey('scope_refreshToken_scopes_id_fk', '{{%oauthScopeRefreshToken}}');
        $this->dropTable('{{%oauthScopeRefreshToken}}');
        return true;
    }

}
