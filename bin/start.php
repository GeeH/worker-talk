<?php
/**
 * Created by PhpStorm.
 * User: GeeH
 * Date: 29/03/2016
 * Time: 09:54
 */
chdir(__DIR__ . '/../');
require('vendor/autoload.php');

$predis = new Predis\Client();
$pheanstalk = new \Pheanstalk\Pheanstalk('127.0.0.1');

$worker = new \Worker\Worker($pheanstalk, $predis);
$worker->start();