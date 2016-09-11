<?php

namespace Piwik\Plugins\QoS;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Period\Range;
use Piwik\Period;
use Piwik\Archive;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\SettingsPiwik;
use Piwik\Site;

/**
 * ExampleUI API is also an example API useful if you are developing a Piwik plugin.
 *
 * The functions listed in this API are returning the data used in the Controller to draw graphs and
 * display tables. See also the ExampleAPI plugin for an introduction to Piwik APIs.
 *
 * @method static \Piwik\Plugins\ExampleUI\API getInstance()
 */
class API extends \Piwik\Plugin\API
{

	public static $disableRandomness = false;


	public function buildDataBwGraph()
	{
		$columns = array('avg_speed');

		$idSite = Common::getRequestVar('idSite', 1);
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getName();

		$now = date("Y-m-d H:i:s");

		$params = array(
			'name'      => $nameCdn,
			'date'      => $now,
			'period'    => '24 hours', // range 24 hours
			'unit'      => 'hour',
			'type'      => 'avg_speed',
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);

		$bandwidthData = array();

		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime )
					{
                        $bandwidthData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = $valueByTime['value'];
					}
				}
			}
		}
		ksort($bandwidthData);
		$graphData = array_slice($bandwidthData, -24, 24, true);
        $tmp = array();
        foreach ( $graphData as $keyTime => $valueByTime )
        {
            $key = explode(" ", $keyTime);
            $tmp[ $key[1]."h" ] = $valueByTime['avg_speed'];
        }
        $graphData = $tmp;
//        echo "<pre>";
//        var_dump($graphData);
//        echo "</pre>";
//		$xAxis = array(
//			'0h', '1h', '2h', '3h', '4h', '5h', '6h', '7h', '8h', '9h', '10h', '11h',
//			'12h', '13h', '14h', '15h', '16h', '17h', '18h', '19h', '20h', '21h', '22h', '23h',
//		);
//
//		$temperatureValues = array_slice(range(50, 90), 0, count($xAxis));
//		if (!self::$disableRandomness) {
//			shuffle($temperatureValues);
//		}
//
//		$temperatures = array();
//		foreach ($xAxis as $i => $xAxisLabel) {
//			$temperatures[$xAxisLabel] = $temperatureValues[$i];
//		}
//		echo "<pre>";
//		var_dump($graphData, $temperatures);
//		echo "</pre>";
		return DataTable::makeFromIndexedArray($graphData);
	}

	public function buildDataHttpCodeGraph()
	{
		$columns = array('request_count_200','request_count_204','request_count_206');

		$idSite = Common::getRequestVar('idSite', 1);
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getName();

		$params = array(
			'name'      => $nameCdn,
			'date'      => date("Y-m-d H:i:s"),
			'period'    => '1 minute',
			'unit'      => 'minute', // range 1 minute
			'type'      => $columns,
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);

		$dataCustomer = array(
			'request_count_200' => 127894,
			'request_count_204' => 29456,
			'request_count_206' => 39344,
		);

		return DataTable::makeFromIndexedArray($dataCustomer);
	}

	public function getEvolutionOverview($idSite, $date, $period, $columns = false)
	{
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getName();

		$typePeriod = $this->countStepPeriod($period);
		$dates      = explode(",", $date);

		if (!$columns) {
			$columns = Common::getRequestVar('columns', false);
		}

		$params = array(
			'name'      => $nameCdn,
			'date'      => ($typePeriod == 'range') ? $date : $dates[1],
			'period'    => ($typePeriod == 'range') ? $typePeriod : $this->diffDays($dates[0], $dates[1]) . ' days',
			'unit'      => $period,
			'type'      => $columns ? $columns : 'request_count_200'
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);

		/**
		 * Make data like
		 *
		 * array (
		 *      "2016-07-17" => array ( "request_count_200" => X, "request_count_500" => Y ),
		 *      "2016-07-18" => array ( "request_count_200" => X, "request_count_500" => Y ),
		 *      "2016-07-19" => array ( "request_count_200" => X, "request_count_500" => Y )
		 * )
		 */

		$dataCustomer = json_decode($dataCustomer, true);
		$graphData = array();

		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime )
					{
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = $valueByTime['value'];
					}
				}
			}
		}
		ksort($graphData);

		return DataTable::makeFromIndexedArray($graphData);
	}

	public function httpCode($idSite, $date, $period, $segment = false)
	{
		if ( !$idSite ) {
			Common::getRequestVar('idSite', false);
		}

		if ( !$date ) {
			Common::getRequestVar('date', false);
		}

		if ( !$period ) {
			Common::getRequestVar('period', 'day');
		}
echo "<pre>";
	var_dump($date, $period);
echo "</pre>";

//		list($strLastDate, $lastPeriod) = Range::getLastDate($date, $period);
//
//echo "<pre>";
//    var_dump($strLastDate, $lastPeriod);
//echo "</pre>";
//
//echo "<pre>";
//    var_dump(Range::getLastDate($date));
//echo "</pre>";
//
//echo "<pre>";
//    var_dump(Date::factory($date)->getDatetime(), Date::factory($date)->getDateStartUTC());
//echo "</pre>";

		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getName();

//		$date = '2016-07-02,2016-08-31';

		$typePeriod = $this->countStepPeriod($period);
		$dates = explode(",", $date);

		$params = array(
			'name'      => $nameCdn,
			'date'      => ($typePeriod == 'range') ? $date : $dates[1],
			'period'    => ($typePeriod == 'range') ? $typePeriod : $this->diffDays($dates[0], $dates[1]) . ' days',
			'unit'      => $period,
			'type'      => 'request_count_200'
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);

		$dataCustomer = json_decode($dataCustomer, true);
		$graphData = array();

		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime )
					{
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = $valueByTime['value'];
					}
				}
			}
		}
		ksort($graphData);

		return DataTable::makeFromIndexedArray($graphData);
	}

	private function apiGetCdnDataMk( $data )
	{
		$url = 'http://125.212.200.247:8001';
		$data['path'] = '/api/v1/stat';

		$query = $data['path']."?name=".$data['name']."&date=".$data['date']."&period=".$data['period']."&unit=".$data['unit']."&type=".$data['type'];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->encodeURI($url.$query));
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, 50000);
		$result = curl_exec($ch);

		$curl_errno = curl_errno($ch);
		if($curl_errno > 0) {
			curl_close($ch);
			return 'timeout';
		}
		curl_close($ch);

		return $result;
	}

	private function encodeURI($url)
	{
		// http://php.net/manual/en/function.rawurlencode.php
		// https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/encodeURI
		$unescaped = array(
			'%2D'=>'-','%5F'=>'_','%2E'=>'.','%21'=>'!', '%7E'=>'~',
			'%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')'
		);
		$reserved = array(
			'%3B'=>';','%2C'=>',','%2F'=>'/','%3F'=>'?','%3A'=>':',
			'%40'=>'@','%26'=>'&','%3D'=>'=','%2B'=>'+','%24'=>'$'
		);
		$score = array(
			'%23'=>'#'
		);
		return strtr(rawurlencode($url), array_merge($reserved,$unescaped,$score));
	}

	private function diffDays($dateFrom, $dateTo)
	{
		$dateTimeFrom = strtotime($dateFrom);
		$dateTimeTo = strtotime($dateTo);

		return ($dateTimeTo - $dateTimeFrom)/86400;
	}

	private function countStepPeriod($period)
	{
		switch ($period)
		{
			case 'week':
			case 'month':
			case 'year';
				$typePeriod = 'range';
				break;
			default:
				$typePeriod = 'days';
				break;
		}

		return $typePeriod;
	}
}
