#! /usr/bin/env php
<?php
use FreeAgent\Bitter\Bitter;
use Dlib\Redis\Driver;

require dirname(__DIR__) . '/common.inc.php';

echo "\n";

if (!Tango\Core\Util::flock(3)) {
	echo 'lock full, exit', "\n";
	exit;
}

$sCategorySetKey = 'lost_user_category';
$oRedis = new Driver();
$oBitter = new Bitter($oRedis);
$lCategory = [];

MQ\Export::setDeleteFile(false);
MQ\Export::reset('lost_user');
$oBitter->removeAll();
$oRedis->del($sCategorySetKey);
$iNum= 0;
foreach (MQ\Export::getLine('lost_user') as list($aMeta, $iTime, $aData)) {

	$sServer = $aMeta['region'];

	foreach ($aData as $aLogData) {

		if (!isset($aLogData['sessId']) || !isset($aLogData['uid']) || !isset($aLogData['category'])) {
			continue;
		}

		$sLogRand = $aLogData['sessId'];
		$uid = $aLogData['uid'];

		$oTime = new DateTime(date("Y-m-d",$iTime));
		$oBitter->mark('login', $aLogData['uid'], $oTime);

		if ($aLogData['category'] === 'panel') {
			foreach ($aLogData['data'] as $index => $aPanelAction) {
				if (is_array($aPanelAction)) {
					$lCategory['panel:'.$aPanelAction['key']] = 1;
					$oBitter->mark('panel:'.$aPanelAction['key'], $aLogData['uid'], $oTime);
				} else {
					if ($index === 'key') {
						$lCategory['panel:'.$aPanelAction] = 1;
						$oBitter->mark('panel:'.$aPanelAction, $aLogData['uid'], $oTime);
					}
				}

			}
		} else {
			$lCategory[$aLogData['category']] = 1;
			$oBitter->mark($aLogData['category'], $aLogData['uid'], $oTime);
		}
	}
	$iNum++;
	if ($iNum % 1000 === 0) {
		$use = microtime(true) -NOW;
		echo "\n",$iNum,"\t", $use, "\t", $use*1000/$iNum, "\n";
	}
}

if ($lCategory) {
	foreach($lCategory as $sCategory => $val) {
		$oRedis->sadd($sCategorySetKey, $sCategory);
	}
}
echo 'Finish ',$iNum,"\n";


