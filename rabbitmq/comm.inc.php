<?php
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__.'/conf.inc.php';

/**
 * 随机生成长度在 $iMinLen-$iMaxLen之间的字符串$iNum个
 * @param $iMinLen
 * @param $iMaxLen
 * @param $iNum
 * @return Generator
 */
function createMsg ($iMinLen, $iMaxLen, $iNum) {
    debug_log("create msgs len: $iMinLen-$iMaxLen; count: $iNum");
    $iMin = max(min($iMinLen, $iMaxLen), 0);
    $iMax = max(max($iMinLen, $iMaxLen), 1);

    for($i=0; $i<$iNum; $i++) {
        $rand = rand($iMin, $iMax);
        yield substr(
            base64_encode(
                file_get_contents('/dev/urandom', false, null, 0, $rand)
            ),
            0,
            $rand
        );
    }
}


define('LOG_LEVEL', 'debug');

function debug_log($sMessage) {
    if (LOG_LEVEL !== 'debug') {
        return;
    }
    static $sID;
    static $sTime;
    if (!$sID) {
        $sID = sprintf('%5d', getmypid());
    }
    if (!$sTime) {
        $sTime = sprintf('%5d', time());
    }
    $sLogFile = '/tmp/rabbit_'.$sTime.'.txt';

    $sMessage = sprintf(
        "%s %s: %s (%s %s)\n",
        date('Y-m-d H:i:s'),
        $sID,
        $sMessage,
        number_format(memory_get_usage()),
        number_format(memory_get_peak_usage())
    );
    echo $sMessage,"\n";
//    file_put_contents($sLogFile, $sMessage, LOCK_EX | FILE_APPEND);
}