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
use PhpAmqpLib\Message\AMQPMessage;

/*
 * send.php的conf
 */
$sQueueName = 'rabbit_mq_1'; //队列名称,和receive.php配对

$iMsgLenMax = 5 * 1024;     //消息长度下限
$iMsgLenMin = 10 * 1024;    //消息长度上限
$iMsgNum = 20000;               //总发送消息数
/*
 * conf end
 */
$iStart = microtime(true);

//create a connection to the server:
$connection = new AMQPConnection(RABBITMQ_HOST, RABBITMQ_PORT, RABBITMQ_USER, RABBITMQ_PASS);
$channel = $connection->channel();

//declare a queue for us to send to
$channel->queue_declare($sQueueName, false, false, false, false);

$iSumTime = 0;
$iSumLen= 0;
$iCurMsgNum = 0;
foreach (createMsg($iMsgLenMin, $iMsgLenMax, $iMsgNum) as $sMsg) {
    $iSumLen += strlen($sMsg);

    $iSendStart = microtime(true);
    $oMsg = new AMQPMessage($sMsg);
    $channel->basic_publish($oMsg, '', $sQueueName);
    $iSendEnd = microtime(true);

    $iSumTime += ($iSendEnd - $iSendStart);
    $iCurMsgNum++;
    if ($iCurMsgNum % 1000 === 0){
        debug_log("Send $iCurMsgNum; Use time: $iSumTime(s); Avg: ". ($iSumTime/$iCurMsgNum)."(s)");
    }
}

//结束，关闭
$channel->close();
$connection->close();

$iEndTime = microtime(true);

debug_log("Send $iMsgNum msgs(size $iMsgLenMax-$iMsgLenMin(KB)); total size: ".($iSumLen/1024)."(KB); Use time: $iSumTime(s); Avg: ". ($iSumTime/$iMsgNum).'s');



