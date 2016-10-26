<?php
/**
 * JwtStorage.php
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


use OAuth2\Storage\JwtBearerInterface;
use sweelix\oauth2\server\models\Jti;
use sweelix\oauth2\server\models\Jwt;

class JwtStorage implements JwtBearerInterface
{
    /**
     * @inheritdoc
     */
    public function getJti($client_id, $subject, $audience, $expiration, $jti)
    {
        $jtiModel = Jti::findOne([
            'clientId' => $client_id,
            'subject' => $subject,
            'audience' => $audience,
            'expires' => $expiration,
            'jti' => $jti,
        ]);
        if ($jtiModel !== null) {
            $finalJti = [
                'issuer' => $jtiModel->clientId,
                'subject' => $jtiModel->subject,
                'audience' => $jtiModel->audience,
                'expires' => $jtiModel->expires,
                'jti' => $jtiModel->jti,
            ];
            $jtiModel = $finalJti;
        }
        return $jtiModel;
    }

    /**
     * @inheritdoc
     */
    public function setJti($client_id, $subject, $audience, $expiration, $jti)
    {
        $jtiModel = new Jti();
        $jtiModel->clientId = $client_id;
        $jtiModel->subject = $subject;
        $jtiModel->audience = $audience;
        $jtiModel->expires = $expiration;
        $jtiModel->jti = $jti;
        $jtiModel->save();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getClientKey($client_id, $subject)
    {
        $jwt = Jwt::findOne([
            'clientId' => $client_id,
            'subject' => $subject,
        ]);
        if ($jwt !== null) {
            $finalJwt = $jwt->publicKey;
            $jwt = $finalJwt;
        }
        return $jwt;
    }
}