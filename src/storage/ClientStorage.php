<?php
/**
 * ClientStorage.php
 *
 * PHP version 5.6+
 *
 * @author pgaultier
 * @copyright 2010-2016 Ibitux
 * @license http://www.ibitux.com/license license
 * @version XXX
 * @link http://www.ibitux.com
 */

namespace sweelix\oauth2\server\storage;


use OAuth2\Storage\ClientCredentialsInterface;
use Yii;

class ClientStorage implements ClientCredentialsInterface
{
    /**
     * @var string
     */
    private $clientClass;

    /**
     * @return string classname for selected interface
     * @since XXX
     */
    public function getClientClass()
    {
        if ($this->clientClass === null) {
            $client = Yii::createObject('sweelix\oauth2\server\interfaces\ClientModelInterface');
            $this->clientClass = get_class($client);
        }
        return $this->clientClass;
    }
    /**
     * @inheritdoc
     */
    public function getClientDetails($client_id)
    {
        $clientClass = $this->getClientClass();
        $client = $clientClass::findOne($client_id);
        if ($client !== null) {
            $finalClient = [
                'redirect_uri' => $client->redirectUri,
                'client_id' => $client->id,
                'grant_types' => $client->grantTypes,
                'user_id' => $client->userId,
                'scope' => implode(' ', $client->scopes),
            ];
            $client = $finalClient;
        }
        return ($client !== null) ? $client : false;
    }

    /**
     * @inheritdoc
     */
    public function getClientScope($client_id)
    {
        $clientClass = $this->getClientClass();
        $client = $clientClass::findOne($client_id);
        $scopes = '';
        if ($client !== null) {
            $scopes = implode(' ', $client->scopes);
        }
        return $scopes;
    }

    /**
     * @inheritdoc
     */
    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        $clientClass = $this->getClientClass();
        $client = $clientClass::findOne($client_id);
        $notRestricted = true;
        if ($client !== null) {
            if (empty($client->grantTypes) === false) {
                $notRestricted = in_array($grant_type, $client->grantTypes);
            }
        }
        return $notRestricted;
    }

    /**
     * @inheritdoc
     */
    public function checkClientCredentials($client_id, $client_secret = null)
    {
        $clientClass = $this->getClientClass();
        $client = $clientClass::findOne($client_id);
        return ($client !== null) ? ($client->secret === $client_secret) : false;
    }

    /**
     * @inheritdoc
     */
    public function isPublicClient($client_id)
    {
        $clientClass = $this->getClientClass();
        $client = $clientClass::findOne($client_id);
        return ($client !== null) ? $client->isPublic : false;
    }
}