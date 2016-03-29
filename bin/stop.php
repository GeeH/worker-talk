<?php

chdir(__DIR__ . '/../');
require('vendor/autoload.php');

$workerId = $argv[1];

$pheanstalk = new \Pheanstalk\Pheanstalk('127.0.0.1');
$job = [
    'type' => 'command',
    'command' => 'stop',
];
$pheanstalk->putInTube($workerId, json_encode($job));