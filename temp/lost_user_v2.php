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

$lCategory = [];

MQ\Export::setDeleteFile(false);
MQ\Export::reset('lost_user');
$oRedis = new Driver();
$oBitter = new Bitter($oRedis, "", "tmp");
$sCategorySetKey = 'lost_user_category';
$oBitter->removeAll();
$oRedis->del($sCategorySetKey);

$iNum= 0;
$oCurTime = new DateTime(date("Y-m-d"));
$iFromTime = strtotime(Date('Y-m-d 00:00:00'));
$iToTime = strtotime(Date('Y-m-d 23:59:59'));
foreach (MQ\Export::getLine('lost_user') as list($aMeta, $iTime, $aData)) {

	$sServer = $aMeta['region'];

	foreach ($aData as $aLogData) {

		if (!isset($aLogData['sessId']) || !isset($aLogData['uid']) || !isset($aLogData['category'])) {
			continue;
		}

		$sLogRand = $aLogData['sessId'];
		$uid = $aLogData['uid'];


		if ($iTime >= $iFromTime && $iTime<=$iToTime) {
			$oTime = $oCurTime;
		} else {
			$oTime = new DateTime(date("Y-m-d", $iTime));
		}
		if ($aLogData['category'] === 'panel') {
			foreach ($aLogData['data'] as $index => $aPanelAction) {
				if (is_array($aPanelAction)) {
					$lCategory[$sServer]['panel:'.$aPanelAction['key']][$aLogData['uid']] = $oTime;
//					$oBitter->mark('panel'.$aPanelAction['key'], $aLogData['uid'], $oTime);
				} else {
					if ($index === 'key') {
						$lCategory[$sServer]['panel:'.$aPanelAction][$aLogData['uid']] = $oTime;
//						$oBitter->mark('panel'.$aPanelAction, $aLogData['uid'], $oTime);
					}
				}

			}
		} else {
			$lCategory[$sServer][$aLogData['category']][$aLogData['uid']] = $oTime;
//			$oBitter->mark($aLogData['category'], $aLogData['uid'], $oTime);
		}
	}
	$iNum++;
	if ($iNum % 5000 === 0) {
		$use = microtime(true) - NOW;
		echo "\ncount: ",$iNum,"\t total use: ", $use, "(s)\t avg user", $use*1000/$iNum, "(s)\n";
		commit($lCategory);
	}
}


function commit($lCategory) {
	$oRedis = new Driver();
	$oBitter = new Bitter($oRedis);
	$sCategorySetKey = 'lost_user_category';

	$i = 0;
	foreach($lCategory as $sServer => $aServerData) {
		foreach($aServerData as $sAction => $aActionData) {
			foreach($aActionData as $uid => $oTime) {
				$oBitter->mark($sServer.':'.$sAction, $uid, $oTime);
				$i++;
			}
			$oRedis->sadd($sCategorySetKey, $sServer.':'.$sAction);
		}
	}
	echo "\nCommit: ",$i,"\n";
}
echo "\nFinish ",$iNum,"\n";


