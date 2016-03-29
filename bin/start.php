<?php
/**
 * Created by PhpStorm.
 * User: GeeH
 * Date: 29/03/2016
 * Time: 09:54
 */
chdir(__DIR__ . '/../');
require('vendor/autoload.php');

$worker = new \Worker\Worker(new \Pheanstalk\Pheanstalk('127.0.0.1'));
$worker->start();