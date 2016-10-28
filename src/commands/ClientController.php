<?php
/**
 * ClientController.php
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package sweelix\oauth2\server\commands
 */

namespace sweelix\oauth2\server\commands;

use yii\console\Controller;
use Yii;

/**
 * Manage oauth clients
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 * @package sweelix\oauth2\server\commands
 * @since XXX
 */
class ClientController extends Controller
{

    public $redirectUri;
    public $grantTypes;
    public $scopes;
    public $userId;
    public $name;
    public $isPublic;

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return [
            // Generated 'id',
            // Generated 'secret',
            'redirectUri',
            'grantTypes',
            'scopes',
            'userId',
            'name',
            'isPublic'
        ];
    }
    /**
     * Create new Oauth client
     * @return int
     * @since XXX
     */
    public function actionCreate()
    {

        $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
        /* @var \sweelix\oauth2\server\interfaces\ClientModelInterface $client */
        $client->id = $this->getRandomString();
        $client->secret = $this->getRandomString();
        $client->redirectUri = $this->redirectUri;
        $client->userId = $this->userId;
        $client->isPublic = (bool)$this->isPublic;
        $client->scopes = empty($this->scope) ? null : explode(',', $this->scopes);
        $client->grantTypes = empty($this->grantTypes) ? null : explode(',', $this->grantTypes);
        if ($client->save() === true) {
            $this->stdout('Client created :'."\n");
            $this->stdout(' - id: ' . $client->id . "\n");
            $this->stdout(' - secret: ' . $client->secret . "\n");
            $this->stdout(' - name: ' . $client->name . "\n");
            $this->stdout(' - redirectUri: ' . $client->redirectUri . "\n");
        } else {
            $this->stdout('Client cannot be created.'."\n");
        }
    }

    /**
     * Generate random string
     * @param int $length
     * @return string
     * @since XXX
     */
    protected function getRandomString($length = 40)
    {
        $bytes = (int) $length/2;
        return bin2hex(openssl_random_pseudo_bytes($bytes));
    }
}