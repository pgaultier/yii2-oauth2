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
use sweelix\oauth2\server\models\Client;

class ClientStorage implements ClientCredentialsInterface
{
    /**
     * @inheritdoc
     */
    public function getClientDetails($client_id)
    {
        $client = Client::findOne($client_id);
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
        $client = Client::findOne($client_id);
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
        $client = Client::findOne($client_id);
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
        $client = Client::findOne($client_id);
        return ($client !== null) ? ($client->secret === $client_secret) : false;
    }

    /**
     * @inheritdoc
     */
    public function isPublicClient($client_id)
    {
        $client = Client::findOne($client_id);
        return ($client !== null) ? $client->isPublic : false;
    }
}