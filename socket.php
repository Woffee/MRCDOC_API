<?php

require 'vendor/autoload.php';

use App\Socket\Server;

$socket = new server();
$socket->start();