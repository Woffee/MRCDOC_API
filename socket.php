<?php

require 'vendor/autoload.php';

use App\Socket\Server;

$socket = new Server();
$socket->start();