<?php

chdir(__DIR__ . '/../');
require('vendor/autoload.php');

$pheanstalk = new \Pheanstalk\Pheanstalk('127.0.0.1');
$job = [
    'type' => 'order',
    'user' => 'GeeH',
    'orderId' => rand(100000,9999999)
];
$pheanstalk->putInTube('jobs', json_encode($job));