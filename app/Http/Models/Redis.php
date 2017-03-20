<?php
/**
 * Created by PhpStorm.
 * User: WF
 * Date: 2017/3/20
 * Time: 16:41
 */

namespace App\Http\Models;

use Predis\Client;

class Redis
{
    protected $client;

    public function __construct()
    {
        $single_server = [
            'host' => env('REDIS_HOST','127.0.0.7'),
            'port' => env('REDIS_PORT',6379),
            'database' => 0,
            'password' => env('REDIS_PASSWORD','123456'),
        ];
        $this->client = new Client( $single_server, ['profile' => '2.8'] );
    }

    public function getClient()
    {
        return $this->client;
    }
}