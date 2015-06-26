<?php
/**
 * Created by PhpStorm.
 * User: dushengchen
 * Date: 15/6/18
 * Time: 上午11:51
 */
require dirname(__DIR__) . '/common.inc.php';


$oDB = Tango\Drive\DB::getInstance('plinga');
$all = file_get_contents('/data/s3_mongo_pay_530.dat');
$arr = explode("\n", $all);
$i = 0;
$oDB->setAutoCreateTable('pay', 'pay_531');
foreach($arr as $sLine) {
	$ll = explode(" ", $sLine);
	if (count($ll) !== 10 || intval($ll[9]) !== 1) {
		echo $sLine,"\n";
		continue;
	}
	$amount = floatval($ll[3]);
	$amount_trans = transPayAmount($amount, $ll[7]);
	if ($amount && !$amount_trans) {
		echo $sLine,"\n";
		continue;
	}
	$i++;
	$sql = 'INSERT IGNORE INTO pay_531 SET '
		. 'mongo_id = "'.addslashes($ll[0]).'", '
		. 'snsid = "'.addslashes($ll[1]).'", '
		. 'uid = '.intval($ll[2]).', '
		. 'usd = '.$amount_trans.', '
		. 'amount = '.$amount.', '
		. 'gameamount = '.floatval($ll[4]).', '
		. 'tid = "'.addslashes($ll[5]).'", '
		. 'pay_time = "'.date('Y-m-d H:i:s', $ll[6]).'", '
		. 'currency = "'.addslashes($ll[7]).'", '
		. 'buy_item = "'.addslashes($ll[8]).'";';
	$oDB->exec($sql);
}
echo "\nFinish: $i/",count($arr);

//function transPayAmount($amount, $currency) {
//	static $exchange = [
//		'VND' => '21669.5000',
//		'NZD' => '1.3332',
//		'HUF' => '271.0850',
//		'GBP' => '0.6609',
//		'COP' => '2395.6700',
//		'MXN' => '15.4394',
//		'PHP' => '44.5850',
//		'AUD' => '1.2699',
//		'PLN' => '3.6144',
//		'EUR' => '0.9000',
//		'THB' => '33.4005',
//		'MYR' => '3.6078',
//		'BRL' => '3.0862',
//		'INR' => '63.4454',
//		'CAD' => '1.2100',
//		'SAR' => '3.7500',
//		'VEF' => '6.3500',
//		'ARS' => '8.9003',
//		'CZK' => '24.6095',
//		'DKK' => '6.7186',
//		'USD' => '1',
//		'FBC' => '10',
//		'CLP' => '614.1950',
//		'ZAR' => '12.0261',
//		'PEN' => '3.1495',
//		'NOK' => '7.6428',
//		'TRY' => '2.7089',
//		'GTQ' => '7.7465',
//		'IDR' => '13055.0000',
//		'SEK' => '8.4016',
//		'SGD' => '1.3360',
//		'AED' => '3.6732',
//		'RUB' => '51.4155',
//		'CHF' => '0.9355',
//		'TWD' => '30.6850',
//		'ILS' => '3.8787',
//		'HKD' => '7.7519',
//		'UYU' => '26.5300',
//		'CNY' => '6.2051',
//		'CRC' => '531.2500',
//		'HRK' => '6.8278',
//		'BAM' => '1.7622',
//	];
//
//	if(isset($exchange[$currency])) {
//		return round($amount / $exchange[$currency], 4);
//	}
//	return 0;
//}