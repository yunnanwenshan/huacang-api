<?php

namespace App\Components;

use Exception;
use Log;

class SimpleToken
{
    protected $clientId;

    protected $clientKey;

    public function __construct($id, $key)
    {
        $this->clientId = $id;
        $this->clientKey = $key;
    }

    protected function sign($payload)
    {
        return sha1($payload.$this->clientKey);
    }

    public function generate()
    {
        $nonce = rand();
        $payload = $this->clientId.'.'.$nonce;

        $sign = $this->sign($payload);
        return $payload.'.'.$sign;
    }

    public function verify($token)
    {
        $pos = strrpos($token, '.');
        if ($pos === false) {
            throw new Exception('Invalid token format', 10);
        }

        $payload = substr($token, 0, $pos);
        $sign = substr($token, $pos + 1);

        if ($sign != $this->sign($payload)) {
            throw new Exception('Signature verify failed', 11);
        }

        $client_id = explode('.', $payload);
        if ($client_id[0] != $this->clientId) {
            throw new Exception('Client dismatch', 12);
        }

        return true;
    }
}