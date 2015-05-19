<?php
/**
 * Created by PhpStorm.
 * User: dushengchen
 * Date: 15/4/2
 * Time: 下午7:00
 */
require 'comm.inc.php';
use dlib\redis\Driver;

$str = 'sd;abfl;sbnfl;djsfnjl;dsnlfsdl;dfnsl;dnsfl;dnsfl';
$key = 'redis_test_key_120u34y1';
$oRedis = new Driver();

var_dump($oRedis->set($key, $str));
var_dump($oRedis->get($key));
var_dump($oRedis->del($key));
var_dump($oRedis->get($key));