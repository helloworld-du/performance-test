#! /usr/bin/env php
<?php
/**
 * 发送消息到queue
 * Created by PhpStorm.
 * User: dushengchen
 * Date: 15/3/25
 * Time: 下午3:04
 */
define('SOURCE_FILE_USER', '/Users/dushengchen/work/git/kt/rabbitmq/demo/user.log');
define('SOURCE_FILE_POST', '/Users/dushengchen/work/git/kt/rabbitmq/demo/post.log');
define('SOURCE_FILE_GIFT', '/Users/dushengchen/work/git/kt/rabbitmq/demo/gift.log');
define('SOURCE_FILE_LOADDATA', '/Users/dushengchen/work/git/kt/rabbitmq/demo/loadData.log');
//compare(SOURCE_FILE_USER);
//compare(SOURCE_FILE_POST);
//compare(SOURCE_FILE_GIFT);
compare(SOURCE_FILE_LOADDATA);

function compare($inputFile) {
    echo "-----------------start---------------\n",date("Y-m-d H:i:s")," ",$inputFile,"\n";
    $iSumNum = $iSumSerializeLen = $iSumJsonLen = 0;
    $res = [
        'json_encode' => 0,
        'json_decode' => 0,
        'serialize' => 0,
        'unserialize' => 0,
    ];
    foreach (readline($inputFile) as $aData) {
        $iSumNum++;
        $time1 = microtime(true);
        $jData = json_encode($aData);
        $time2 = microtime(true);
        $sData = serialize($aData);

        $time3 = microtime(true);
        $ujData = json_decode($jData, true);

        $time4 = microtime(true);
        $usData = unserialize($sData);
        $time5 = microtime(true);

        $iSumJsonLen += strlen($jData);
        $iSumSerializeLen += strlen($sData);

        $res['json_encode'] += ($time2 - $time1);
        $res['serialize'] += ($time3 - $time2);
        $res['json_decode'] += ($time4 - $time3);
        $res['unserialize'] += ($time5 - $time4);
        if ($iSumNum % 2000 == 0) {
            echo "Msgs: ",$iSumNum,"\n";
            echo "\tJson Len: ",$iSumJsonLen/1024,"(KB); Avg Len: ",$iSumJsonLen/$iSumNum,"(B);\n";
            echo "\tserialize Len: ",$iSumSerializeLen/1024,"(KB); Avg Len: ",$iSumSerializeLen/$iSumNum,"(B);\n";
            echo "\tTime use: ",json_encode($res),"\n";
        }
    }
    echo "\n-------------------Result-----------------\n";
    echo "Msgs: ",$iSumNum,"\n";
    echo "Json Len: ",$iSumJsonLen/1024,"(KB); Avg: ",$iSumJsonLen/$iSumNum,"(B);\n";
    echo "serialize Len: ",$iSumSerializeLen/1024,"(KB); Avg: ",$iSumSerializeLen/$iSumNum,"(B);\n";
    echo json_encode($res),"\n";
    echo date("Y-m-d H:i:s")," ",$inputFile,"\n-------------------end-----------------\n";
}

function readline($inputFile) {
    $sFileData = file_get_contents($inputFile);
    if (!$sFileData) {
        return;
    }
    $lFileData = explode("\n", $sFileData);
    foreach ($lFileData as $sFileLine) {
        $lFileLine = explode(",", $sFileLine, 3);
        if (count($lFileLine) <3 || !$lFileLine[2]) {
            continue;
        }
        $aFileLine = json_decode($lFileLine[2], true);
        if (!$aFileLine) {
            continue;
        }
        yield $aFileLine;
    }
}






