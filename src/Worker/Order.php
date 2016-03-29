<?php
/**
 * Created by PhpStorm.
 * User: GeeH
 * Date: 29/03/2016
 * Time: 10:38
 */

namespace Worker;


class Order
{

    public static function handle(array $decodedJob)
    {
        echo 'Order ' . $decodedJob['orderId'] . ' by ' . $decodedJob['user'] . PHP_EOL;
    }
}