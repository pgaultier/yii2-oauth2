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

class JwtStorage implements JwtBearerInterface
{
    public function getClientKey($client_id, $subject)
    {
        // TODO: Implement getClientKey() method.
    }
}