#! /usr/bin/env php
<?php
/**
 * 发送消息到queue
 * Created by PhpStorm.
 * User: dushengchen
 * Date: 15/3/25
 * Time: 下午3:04
 */
$iStartTime = microtime(true);
require __DIR__.'/comm.inc.php';

use PhpAmqpLib\Connection\AMQPConnection;
/*
 * send.php的conf
 */
$sQueueName = 'rabbit_mq_1'; //队列名称
define('QUIT_NUM', 100000); //接受多少条消息后推出

/*
 * conf end
 */

$callback = function($oMsg) use(&$iMsgLen){
    //static $iMsgNum;
    $iMsgLen += strlen($oMsg->body);
};


//create a connection to the server:
$connection = new AMQPConnection(RABBITMQ_HOST, RABBITMQ_PORT, RABBITMQ_USER, RABBITMQ_PASS);
$channel = $connection->channel();

//declare a queue for us to send to
$channel->queue_declare($sQueueName, false, false, false, false);

//define a PHP callable that will receive the messages sent by the server
$channel->basic_consume($sQueueName, '', false, true, false, false, $callback);

$iStart = microtime(true);
$iMsgNum = 0;
global $iMsgLen;
$iMsgLen = 0;
$iSumTime = 0;
//loop,and whenever we receive a message our $callback function will be passed the received message
while(count($channel->callbacks)) {
    $iRevStart = microtime(true);
    $channel->wait();
    $iSumTime += (microtime(true) - $iRevStart);
    $iMsgNum++;

    if ($iMsgNum % 5000 === 0) {
        debug_log("Received $iMsgNum msgs; Total size: ".($iMsgLen/1024)."(KB); Use time: $iSumTime(s); Avg: ".($iSumTime/$iMsgNum) ."(s)");
    }
    if ($iMsgNum >= QUIT_NUM) {
        debug_log("Received $iMsgNum msgs; Total size: ".($iMsgLen/1024)."(KB); Use time: $iSumTime(s); Avg: ".($iSumTime/$iMsgNum) ."(s)");
        break;
    }
}
debug_log("Received $iMsgNum msgs; Total size: ".($iMsgLen/1024)."(KB); Use time: $iSumTime(s); Avg: ".($iSumTime/$iMsgNum) ."(s)");




