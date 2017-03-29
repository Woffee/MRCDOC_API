<?php

/*
 * Sometime too hot the eye of heaven shines
 */

namespace App\Socket;

use Predis\Client;

class Redis
{
    protected $client;

    protected $single_server = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 00,
        'password' => 123456,
    ];

    public function __construct()
    {
        $this->client = new Client($this->single_server, ['profile' => '2.8']);
    }

    public function getClient()
    {
        return $this->client;
    }
}