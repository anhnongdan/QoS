<?php

namespace Piwik\Plugins\QoS;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Site;
use Piwik\Metrics\Formatter;

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
	private $overview = array(
		'traffic_ps'    => 'traffic_ps',
		'avg_speed'     => 'avg_speed',
		'body_bytes_sent' => 'body_bytes_sent'
	);
	private $traffic = array(
		'isp_traffic_ps_total',
		'isp_traffic_ps_vnpt',
		'isp_traffic_ps_vinaphone',
		'isp_traffic_ps_viettel',
		'isp_traffic_ps_fpt',
		'isp_traffic_ps_mobiphone'
	);
	private $cacheHit = array(
		'edge_hit'  => array(
			'hit_total' => 'isp_request_count_2xx_total',
			'hit_vnpt'  => 'isp_request_count_2xx_vnpt',
			'hit_vinaphone' => 'isp_request_count_2xx_vinaphone',
			'hit_viettel'   => 'isp_request_count_2xx_viettel',
			'hit_fpt'       => 'isp_request_count_2xx_fpt',
			'hit_mobiphone' => 'isp_request_count_2xx_mobiphone',
		),
		'ratio_hit' => array(
			'ratio_total' => 'cache_status_HIT,request_count_200,request_count_206',
			'ratio_vnpt'  => 'isp_cache_status_HIT_vnpt,isp_request_count_200_vnpt,isp_request_count_206_vnpt',
			'ratio_fpt'   => 'isp_cache_status_HIT_fpt,isp_request_count_200_fpt,isp_request_count_206_fpt',
			'ratio_viettel'   => 'isp_cache_status_HIT_viettel,isp_request_count_200_viettel,isp_request_count_206_viettel',
			'ratio_mobiphone' => 'isp_cache_status_HIT_mobiphone,isp_request_count_200_mobiphone,isp_request_count_206_mobiphone',
			'ratio_vinaphone' => 'isp_cache_status_HIT_vinaphone,isp_request_count_200_vinaphone,isp_request_count_206_vinaphone',
		)
	);

	private $httpCode = array(
		'2xx'   => array('request_count_200','request_count_204','request_count_206'),
		'3xx'   => array('request_count_301','request_count_302','request_count_304'),
		'4xx'   => array('request_count_400','request_count_404'),
		'5xx'   => array('request_count_500','request_count_502','request_count_503','request_count_504')
	);

	public function __construct() {
		$timezone = Site::getTimezoneFor( Common::getRequestVar('idSite', 1) );
		if ( $timezone ) {
			date_default_timezone_set( $timezone );
		} else {
			date_default_timezone_set('Asia/Ho_Chi_Minh');
		}
	}

	public function getOverview() {
		return $this->overview;
	}

	public function getTraffic() {
		return $this->traffic;
	}


	public function getHttpCode() {
		return $this->httpCode;
	}

	public function getCacheHit() {
		return $this->cacheHit;
	}


	public function buildDataBwGraph()
	{
		$rollups = \Piwik\API\Request::processRequest('RollUpReporting.getRollUps', array());

		$idSite = Common::getRequestVar('idSite', 1);
                foreach( $rollups as $rollup) {
                        if($idSite == $rollup['idsite']) {
                                return $this->buildDataBwGraphFRU($rollup['sourceIdSites']);
                        }
                }
                return $this->buildDataBwGraphFSS($idSite);
	}		

	protected function buildDataBwGraphFRU($idSites) 
	{
		$total = null;
		foreach($idSites as $idSite) {
			$dataTable = $this->buildDataBwGraphFSS($idSite);
			if(!$dataTable->getColumns()) {
				continue;
			}
			if($total === null){
				$total = $dataTable;
				continue;
			}
			$total->addDataTable($dataTable);
		}
		return $total;
	}

	protected function buildDataBwGraphFSS($idSite)
	{
		$columns = array('avg_speed');

		//$idSite = Common::getRequestVar('idSite', 1);
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
		$nameCdn    = explode("//",$nameCdn)[1];

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
			// $key = explode(" ", $keyTime);
			// $tmp[ $key[1]."h" ] = $valueByTime['avg_speed'];
			$tmp[ $keyTime."h" ] = $valueByTime['avg_speed'];
		}
		$graphData = $tmp;

		return DataTable::makeFromIndexedArray($graphData);
	}

	public function buildDataHttpCodeGraph()
	{
		$columns = 'request_count_200,request_count_204,request_count_206';

		$idSite = Common::getRequestVar('idSite', 1);
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
		$nameCdn    = explode("//",$nameCdn)[1];

		$qosLastMinuteUpdateSetting = new SystemSettings('qosLastMinuteUpdate');
		$lastMinutes = $qosLastMinuteUpdateSetting->qosLastMinuteUpdate->getValue();
		if ( $lastMinutes < 1 ) {
			$lastMinutes = 5;
		}
		$now = time();
		$before_3mins = $now - ($lastMinutes * 60);
		$date_param = date("Y-m-d H:i:s", $before_3mins).",".date("Y-m-d H:i:s", $before_3mins);
		// $date_param = date("Y-m-d H:i:s").",".date("Y-m-d H:i:s");
		$params = array(
			'name'      => $nameCdn,
			'date'      => "$date_param",
			'period'    => 'range',
			'unit'      => 'day', // range 1 minute
			'type'      => $columns,
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
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = (int)$valueByTime['value'];
					}
				}
			}
		}

		return DataTable::makeFromIndexedArray(current($graphData));
	}

	public function buildDataIspGraph()
	{
		$columns = 'isp_request_count_200_mobiphone,isp_request_count_200_vinaphone,isp_request_count_200_fpt,isp_request_count_200_viettel,isp_request_count_200_vnpt';

		$idSite = Common::getRequestVar('idSite', 1);
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
		$nameCdn    = explode("//",$nameCdn)[1];

		$date_param = date("Y-m-d H:i:s").",".date("Y-m-d H:i:s");
		$params = array(
			'name'      => $nameCdn,
			'date'      => "$date_param",
			'period'    => 'range',
			'unit'      => 'day', // range 1 minute
			'type'      => $columns,
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
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = (int)$valueByTime['value'];
					}
				}
			}
		}

		return DataTable::makeFromIndexedArray(current($graphData));
	}

	public function buildDataCountryGraph()
	{
		$columns = 'country_request_count_200_VN,country_request_count_200_US,country_request_count_200_CN';

		$idSite = Common::getRequestVar('idSite', 1);
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
		$nameCdn    = explode("//",$nameCdn)[1];

		$date_param = date("Y-m-d H:i:s").",".date("Y-m-d H:i:s");
		$params = array(
			'name'      => $nameCdn,
			'date'      => "$date_param",
			'period'    => 'range',
			'unit'      => 'day', // range 1 minute
			'type'      => $columns,
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
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = (int)$valueByTime['value'];
					}
				}
			}
		}

		return DataTable::makeFromIndexedArray(current($graphData));
	}

	public function overViewSpeedGraph($idSite, $metric)
	{
		if(!$idSite) {
			$idSite = Common::getRequestVar('idSite', 1);
		}

		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
		$nameCdn    = explode("//",$nameCdn)[1];

		$date_param = date("Y-m-d H:i:s").",".date("Y-m-d H:i:s");
		$params = array(
			'name'      => $nameCdn,
			'date'      => "$date_param",
			'period'    => 'range',
			'unit'      => 'minute', // range 1 minute
			'type'      => $metric ? $metric : 'avg_speed',
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
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = (int)$valueByTime['value'];
					}
				}
			}
		}

		$userSpeed  = current(current($graphData));
		$maxtime    = $userSpeed * 1.5;

		return array(
			'maxtime'       => (int)$maxtime,
			'user_speed'    => (int)$userSpeed
		);
	}

	public function overViewCacheHitGraph($idSite, $metric)
	{
		if(!$idSite) {
			$idSite = Common::getRequestVar('idSite', 1);
		}

		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
		$nameCdn    = explode("//",$nameCdn)[1];

		$date_param = date("Y-m-d H:i:s").",".date("Y-m-d H:i:s");
		$params = array(
			'name'      => $nameCdn,
			'date'      => "$date_param",
			'period'    => 'range',
			'unit'      => 'minute', // range 1 minute
			'type'      => $metric ? $metric : 'isp_request_count_200_viettel',
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
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = (int)$valueByTime['value'];
					}
				}
			}
		}

		$cacheHit   = current(current($graphData));
		$maxtime    = $cacheHit * 1.5;

		return array(
			'maxtime'       => (int)$maxtime,
			'cache_hit'     => (int)$cacheHit
		);
	}

	public function getEvolutionOverview($idSite, $date, $period, $columns = false)
	{
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
		$nameCdn    = explode("//",$nameCdn)[1];

		$module = Common::getRequestVar('module', false);
		$action = Common::getRequestVar('action', false);

		$typePeriod = $this->countStepPeriod($period);
		$dates      = explode(",", $date);

		if (!$columns) {
			$columns = Common::getRequestVar('columns', false);
			if( !$columns && $module == 'QoS' && $action == 'httpCode' ) {
				$columns = $this->httpCode;
			}
		}

		if ( is_array($columns) ) {
			$columns = implode(",",$columns);
		}

		$params = array(
			'name'      => $nameCdn,
			'date'      => ($typePeriod == 'range') ? $date : $dates[1],
			'period'    => ($typePeriod == 'range') ? $typePeriod : $this->diffDays($dates[0], $dates[1]) . ' days',
			'unit'      => $period,
			'type'      => $columns ? $columns : 'request_count_200,request_count_204,request_count_206,request_count_301,request_count_302,request_count_304'
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


	public function getGraphEvolution($idSite, $date, $period, $columns = false)
	{
                $rollups = \Piwik\API\Request::processRequest('RollUpReporting.getRollUps', array());

                $idSite = Common::getRequestVar('idSite', 1);
                foreach( $rollups as $rollup) {
                        if($idSite == $rollup['idsite']) {
                                return $this->getGraphEvolutionFRU($rollup['sourceIdSites'], $date, $period, $columns);
                        }
                }
                return $this->getGraphEvolutionFSS($idSite, $date, $period, $columns);
        }

        protected function getGraphEvolutionFRU($idSites, $date, $period, $columns = false)
        {
                $total = null;
                foreach($idSites as $idSite) {
                        $dataTable = $this->getGraphEvolutionFSS($idSite, $date, $period, $columns);
                        if(!$dataTable->getColumns()) {
                                continue;
                        }
                        if($total === null){
                                $total = $dataTable;
                                continue;
                        }
			// \Piwik\Log::warning(var_dump($dataTable));
                        //$total->addDataTable($dataTable);
                }
		//var_dump($total);
                return $total;
        }


	protected function getGraphEvolutionFSS($idSite, $date, $period, $columns = false)
	{
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
		$nameCdn = explode("//",$nameCdn)[1];

		$module = Common::getRequestVar('module', false);
		$action = Common::getRequestVar('action', false);

		$isp    = Common::getRequestVar('isp',    false);
		$metric    = Common::getRequestVar('metric',    false);
		$statusCode = Common::getRequestVar('statusCode',    false);

		$typePeriod = $this->countStepPeriod($period);
		$dates      = explode(",", $date);

		if (!$columns) {
			$columns = Common::getRequestVar('columns', false);
			if( !$columns && $module == 'QoS' && $action == 'overview' ) {
				if ( $metric ){
					$columns = $this->overview[ $metric ];
				} else {
					$columns = array();
					$columns[] = implode(",",$this->overview);
				}
			} elseif( !$columns && $module == 'QoS' && $action == 'mnBandwidth' ) {
				if ( $isp ){
					$columns = $this->traffic[$isp];
				} else {
					$columns = array();
					foreach ($this->traffic as $metrics) {
						$columns[] = implode(",",$metrics);
					}
				}

			} elseif( !$columns && $module == 'QoS' && $action == 'httpCode' ) {
				if ( $statusCode ){
					$columns = $this->httpCode[$statusCode];
				} else {
					$columns = array();
					foreach ($this->httpCode as $metrics) {
						$columns[] = implode(",",$metrics);
					}
				}
			} elseif (!$columns && $module == 'QoS' && $action == 'cacheHit') {
				if ( $isp ){
					$columns = $this->cacheHit[$isp];
				} else {
					$columns = array();
					foreach ($this->cacheHit as $metrics) {
						$columns[] = implode(",",$metrics);
					}
				}
			} elseif (!$columns && $module == 'QoS' && $action == 'mnSizeTraffic') {
				$columns = $this->userSpeed;
			} elseif (!$columns && $module == 'QoS' && $action == 'isp') {
				if ( $isp ){
					$columns = $this->isp[$isp];
				} else {
					$columns = array();
					foreach ($this->isp as $metrics) {
						$columns[] = implode(",",$metrics);
					}
				}
			} elseif (!$columns && $module == 'QoS' && $action == 'country') {
				$columns = $this->country;
			}
		}

		if ( is_array($columns) ) {
			$columns = implode(",",$columns);
		}

		$params = array(
			'name'      => $nameCdn,
			'date'      => ($typePeriod == 'range') ? $date : $dates[1],
			'period'    => ($typePeriod == 'range') ? $typePeriod : $this->diffDays($dates[0], $dates[1]) . ' days',
			'unit'      => $period,
			'type'      => $columns
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
		$format = new Formatter();
		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime ) {
						if( $module == 'QoS' && $action == 'overview' && $metric == 'body_bytes_sent') {
							\Piwik\Log::warning('pretty size for overview BW: ', getPrettySizeFromBytes($valueByTime['value'], 'G'));
							$graphData[$valueByTime['name']][$valueOfTypeRequest['type']] = $format->getPrettySizeFromBytes($valueByTime['value'], 'G');
							//$graphData[$valueByTime['name']][$valueOfTypeRequest['type']] = $valueByTime['value'];
						} else {
							$graphData[$valueByTime['name']][$valueOfTypeRequest['type']] = $format->getPrettySizeFromBytes($valueByTime['value'], 'M');

							//$graphData[$valueByTime['name']][$valueOfTypeRequest['type']] = $valueByTime['value'];
						}
					}
				}
			}
		}

		ksort($graphData);

		$r = DataTable::makeFromIndexedArray($graphData);
		return $r;
	}

	public function getGraphEvolutionCacheHit($idSite, $date, $period, $columns = false)
	{
                $rollups = \Piwik\API\Request::processRequest('RollUpReporting.getRollUps', array());

                $idSite = Common::getRequestVar('idSite', 1);
                foreach( $rollups as $rollup) {
                        if($idSite == $rollup['idsite']) {
                                return $this->getGraphEvolutionCacheHitFRU($rollup['sourceIdSites'], $date, $period, $columns);
                        }
                }
                return $this->getGraphEvolutionCacheHitFSS($idSite, $date, $period, $columns);
		
	}	

	public function getGraphEvolutionCacheHitFRU($idSites, $date, $period, $columns = false)
	{
                $total = null;
                foreach($idSites as $idSite) {
                        $dataTable = $this->getGraphEvolutionCacheHitFSS($idSite, $date, $period, $columns);
                        if(!$dataTable->getColumns()) {
                                continue;
                        }
                        if($total === null){
                                $total = $dataTable;
                                continue;
                        }
			// \Piwik\Log::warning(var_dump($dataTable));
                        $total->addDataTable($dataTable);
                }
		//var_dump($total);
                //\Piwik\Log::warning('total columns: '.implode(', ', $total->getRows()) );
		
		/**
		*[Thangnt 2017-07-18] Make a dedicated DataTable filter when needed.
		*/	
		if($total->getColumn('ratio_total') !== false) {
			$sitesN = count($idSites);
			$total->filter( function($table) use ($sitesN) {
				foreach($table->getRows() as $row) {
					$ratio = round($row->getColumn('ratio_total')/$sitesN, 1);
					$row->setColumn('ratio_total', $ratio);
				}
			});
		} 
		return $total;
	}	

	/**
	* Called from getGraphEvolution when action of the QoS plugin is cacheHit
	*/
	public function getGraphEvolutionCacheHitFSS($idSite, $date, $period, $columns = false)
	{
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
		$nameCdn = explode("//",$nameCdn)[1];

		$module = Common::getRequestVar('module', false);
		$action = Common::getRequestVar('action', false);
		$metric = Common::getRequestVar('metric', false);

		$typePeriod = $this->countStepPeriod($period);
		$dates      = explode(",", $date);

		if (!$columns) {
			$columns = Common::getRequestVar('columns', false);
			if (!$columns ) {
				if ( $metric ){
					$columns = current(array_keys($this->cacheHit[ $metric ]));
				} else {
					foreach ($this->cacheHit as $m) {
						foreach ($m as $k => $v){
							$columns[] = $k;
						}
					}
				}
			}
		}
		$colArr = array();
		if ($metric == 'ratio_hit') {
			if ( !is_array($columns) ) {
				$columns = explode(",",$columns);
			}
			$colArr = $columns;
		}

		if ( is_array($columns) ) {
			$columns = implode(",",$columns);
		}

		$graphData  = array();
		/*
		 * List metric to call api, always full
		 */
		if ( $metric == 'edge_hit' ) {
			$colList = array_diff($this->cacheHit[ $metric ], array('isp_request_count_2xx_total'));
			$colList = implode(",", $colList);

			$params = array(
				'name'      => $nameCdn,
				'date'      => ($typePeriod == 'range') ? $date : $dates[1],
				'period'    => ($typePeriod == 'range') ? $typePeriod : $this->diffDays($dates[0], $dates[1]) . ' days',
				'unit'      => $period,
				'type'      => $colList
			);
			$dataCustomer = $this->apiGetCdnDataMk($params);
			$dataCustomer = json_decode($dataCustomer, true);

			if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
			{
				foreach ( $dataCustomer['data'] as $valueOfCdn )
				{
					// Name of Cdn: $valueOfCdn['name']
					foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
					{
						// Type request: valueOfTypeRequest['type']
						foreach ( $valueOfTypeRequest['value'] as $valueByTime ) {
							$keyName = array_search($valueOfTypeRequest['type'], $this->cacheHit[ $metric ]);
							if ( strpos($columns,$keyName) !== false ) {
								$graphData[$valueByTime['name']][ $keyName ] = (int)$valueByTime['value'];
							}
							if (isset($graphData[$valueByTime['name']]['hit_total'])) {
								$graphData[$valueByTime['name']]['hit_total'] += (int)$valueByTime['value'];
							} else {
								$graphData[$valueByTime['name']]['hit_total'] = (int)$valueByTime['value'];
							}
						}
					}
				}
			}

		} elseif ( $metric == 'ratio_hit' ) {

			foreach ($colArr as $c) {
				$colList = $this->cacheHit[ $metric ][ $c ];
				$params = array(
					'name'      => $nameCdn,
					'date'      => ($typePeriod == 'range') ? $date : $dates[1],
					'period'    => ($typePeriod == 'range') ? $typePeriod : $this->diffDays($dates[0], $dates[1]) . ' days',
					'unit'      => $period,
					'type'      => $colList
				);
				$reRatio = $this->comRatioHit($params,$c,explode(",",$colList));
				
				$graphData = array_merge_recursive($graphData,$reRatio);
			}
		}
		ksort($graphData);

		return DataTable::makeFromIndexedArray($graphData);
	}

	private function comRatioHit($params, $label, $colArr) {

		//\Piwik\Log::warning(json_encode($params));
		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);

		$result = array();
		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				//\Piwik\Log::warning(json_encode($valueOfCdn));
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					foreach ($valueOfTypeRequest['value'] as $valueByTime)
					{
						if (isset($result[$valueByTime['name']][ $valueOfTypeRequest['type'] ])) {
							$result[$valueByTime['name']][ $valueOfTypeRequest['type'] ] += (int)$valueByTime['value'];
						} else {
							$result[$valueByTime['name']][ $valueOfTypeRequest['type'] ] = (int)$valueByTime['value'];
						}
					}
				}
			}
		}

		$arrTmp = array();
		foreach ($result as $date => $val) {
			if(($val[ $colArr[1] ] + $val[ $colArr[2] ]) == 0 ) {
				$arrTmp[ $date ][ $label ] = 0;
				continue;
			}
			$t = round($val[ $colArr[0] ]/( $val[ $colArr[1] ] + $val[ $colArr[2] ] ), 2);
			$arrTmp[ $date ][ $label ] = $t * 100;
		}
		$result = $arrTmp;

		return $result;
	}

	public function getGraphEvolutionISP($idSite, $date, $period, $columns = false)
	{
                $rollups = \Piwik\API\Request::processRequest('RollUpReporting.getRollUps', array());

                $idSite = Common::getRequestVar('idSite', 1);
                foreach( $rollups as $rollup) {
                        if($idSite == $rollup['idsite']) {
                                return $this->getGraphEvolutionISPFRU($rollup['sourceIdSites'], $date, $period, $columns);
                        }
                }
                return $this->getGraphEvolutionISPFSS($idSite, $date, $period, $columns);
		
	}	

	public function getGraphEvolutionISPFRU($idSites, $date, $period, $columns = false)
	{
                $total = null;
                foreach($idSites as $idSite) {
                        $dataTable = $this->getGraphEvolutionISPFSS($idSite, $date, $period, $columns);
                        if(!$dataTable->getColumns()) {
                                continue;
                        }
                        if($total === null){
                                $total = $dataTable;
                                continue;
                        }
			// \Piwik\Log::warning(var_dump($dataTable));
                        $total->addDataTable($dataTable);
                }
		//var_dump($total);
                return $total;
	}	


	public function getGraphEvolutionISPFSS($idSite, $date, $period, $columns = false)
	{
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
		$nameCdn    = explode("//",$nameCdn)[1];

		$module = Common::getRequestVar('module', false);
		$action = Common::getRequestVar('action', false);

		$typePeriod = $this->countStepPeriod($period);
		$dates      = explode(",", $date);

		if (!$columns) {
			$columns = Common::getRequestVar('columns', false);
			if (!$columns && $module == 'QoS' && $action == 'isp') {
				$columns = $this->traffic;
			}
		}

		if ( is_array($columns) ) {
			if (in_array('isp_traffic_ps_total', $columns)) {
				$columns = array_diff($columns, array('isp_traffic_ps_total'));
			}
			$columns = implode(",",$columns);
		}

		if ( strrpos( $columns, 'isp_traffic_ps_total') ) {
			$columns = explode(",",$columns);
			$columns = array_diff($columns, array('isp_traffic_ps_total'));
			$columns = implode(",",$columns);
		}

		$columns2 = $this->traffic;
		if ( is_array($columns2) ) {
			if (in_array('isp_traffic_ps_total', $columns2)) {
				$columns2 = array_diff($columns2, array('isp_traffic_ps_total'));
			}
			$columns2 = implode(",",$columns2);
		}

		$params = array(
			'name'      => $nameCdn,
			'date'      => ($typePeriod == 'range') ? $date : $dates[1],
			'period'    => ($typePeriod == 'range') ? $typePeriod : $this->diffDays($dates[0], $dates[1]) . ' days',
			'unit'      => $period,
			'type'      => $columns2
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);
		$graphData  = array();
		$format = new Formatter();
		if ( $dataCustomer['status'] == 'true' && $dataCustomer['data'] )
		{
			foreach ( $dataCustomer['data'] as $valueOfCdn )
			{
				// Name of Cdn: $valueOfCdn['name']
				foreach ( $valueOfCdn['value'] as $valueOfTypeRequest )
				{
					// Type request: valueOfTypeRequest['type']
					foreach ( $valueOfTypeRequest['value'] as $valueByTime ) {

						if ( strpos($columns, $valueOfTypeRequest['type']) !== false ) {
							//$graphData[$valueByTime['name']][$valueOfTypeRequest['type']] = $format->getPrettySizeFromBytes((int)$valueByTime['value'], "M");
							$graphData[$valueByTime['name']][$valueOfTypeRequest['type']] = (int)$valueByTime['value'];
						}
						if ( isset($graphData[ $valueByTime['name'] ][ 'isp_traffic_ps_total' ]) ) {
							//$graphData[$valueByTime['name']]['isp_traffic_ps_total'] += $format->getPrettySizeFromBytes((int)$valueByTime['value'], "M");
							$graphData[$valueByTime['name']]['isp_traffic_ps_total'] += (int)$valueByTime['value'];
						} else {
							//$graphData[$valueByTime['name']]['isp_traffic_ps_total'] = $format->getPrettySizeFromBytes((int)$valueByTime['value'], "M");
							$graphData[$valueByTime['name']]['isp_traffic_ps_total'] = (int)$valueByTime['value'];
						}
					}
				}
			}
		}

		ksort($graphData);

		return DataTable::makeFromIndexedArray($graphData);
	}

	public function getGraphEvolutionBandwidth($idSite, $date, $period, $columns = false)
	{
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
		$nameCdn    = explode("//",$nameCdn)[1];

		$module = Common::getRequestVar('module', false);
		$action = Common::getRequestVar('action', false);

		$isp    = Common::getRequestVar('isp',    false);

		$typePeriod = $this->countStepPeriod($period);
		$dates      = explode(",", $date);

		if (!$columns) {
			$columns = Common::getRequestVar('columns', false);
			if (!$columns && $module == 'QoS' && $action == 'mnBandwidth') {
				if ($isp) {
					$columns = $this->getTraffic()[$isp];
				} else {
					$columns = array();
					foreach ($this->getTraffic() as $metrics) {
						$columns[] = implode(",", $metrics);
					}
				}
			}
		}
		if (in_array('isp_isp_traffic_ps_total', $columns)) {
			unset($columns['isp_isp_traffic_ps_total']);
		}
		if ( is_array($columns) ) {
			$columns = implode(",",$columns);
		}

		$params = array(
			'name'      => $nameCdn,
			'date'      => ($typePeriod == 'range') ? $date : $dates[1],
			'period'    => ($typePeriod == 'range') ? $typePeriod : $this->diffDays($dates[0], $dates[1]) . ' days',
			'unit'      => $period,
			'type'      => $columns
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);
		$graphData  = array();
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
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ]    = $valueByTime['value'];
						$graphData[ $valueByTime['name'] ][ 'isp_isp_traffic_ps_total' ]    += $valueByTime['value'];
					}
				}
			}
		}
		ksort($graphData);

		return DataTable::makeFromIndexedArray($graphData);
	}

	public function getGraphEvolutionAvgSpeed($idSite, $date, $period, $columns = false)
	{
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
		$nameCdn    = explode("//",$nameCdn)[1];

		$module = Common::getRequestVar('module', false);
		$action = Common::getRequestVar('action', false);

		$isp    = Common::getRequestVar('isp',    false);

		$typePeriod = $this->countStepPeriod($period);
		$dates      = explode(",", $date);

		if (!$columns) {
			$columns = Common::getRequestVar('columns', false);
			if (!$columns && $module == 'QoS' && $action == 'mnSizeTraffic') {
				if ($isp) {
					$columns = $this->ispSpeedDownload[$isp];
				} else {
					$columns = array();
					foreach ($this->ispSpeedDownload as $metrics) {
						$columns[] = implode(",", $metrics);
					}
				}
			}
		}

		if (in_array('isp_avg_speed_total', $columns)) {
			unset($columns['isp_avg_speed_total']);
		}
		if ( is_array($columns) ) {
			$columns = implode(",",$columns);
		}

		$params = array(
			'name'      => $nameCdn,
			'date'      => ($typePeriod == 'range') ? $date : $dates[1],
			'period'    => ($typePeriod == 'range') ? $typePeriod : $this->diffDays($dates[0], $dates[1]) . ' days',
			'unit'      => $period,
			'type'      => $columns
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);
		$graphData  = array();
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
						$graphData[ $valueByTime['name'] ][ 'isp_avg_speed_total' ] += $valueByTime['value'];
					}
				}
			}
		}
		ksort($graphData);

		return DataTable::makeFromIndexedArray($graphData);
	}

	public function getBrowsers($idSite, $period, $date, $segment = false)
	{

		$data = \Piwik\API\Request::processRequest('DevicesDetection.getBrowsers', array(
			'idSite'    => $idSite,
			'period'    => $period,
			'date'      => $date,
			'segment'   => $segment,
		));
		$data->applyQueuedFilters();

		$result = $data->getEmptyClone($keepFilters = false);

		foreach ($data->getRows() as $visitRow) {
			$browserName = $visitRow->getColumn('label');

			$result->addRowFromSimpleArray(array(
				'label'             => $browserName,
				'nb_uniq_visitors'  => $visitRow->getColumn('nb_uniq_visitors')
			));
		}

		return $result;
	}

	public function getCity($idSite, $period, $date, $segment = false)
	{

		$data = \Piwik\API\Request::processRequest('UserCountry.getCity', array(
			'idSite'    => $idSite,
			'period'    => $period,
			'date'      => $date,
			'segment'   => $segment,
		));
		$data->applyQueuedFilters();

		$result = $data->getEmptyClone($keepFilters = false);

		foreach ($data->getRows() as $visitRow) {
			$browserName = $visitRow->getColumn('label');

			$result->addRowFromSimpleArray(array(
				'label'             => $browserName,
				'nb_uniq_visitors'  => $visitRow->getColumn('nb_uniq_visitors')
			));
		}

		return $result;
	}

	public function getUrls($idSite, $period, $date, $segment = false)
	{

		$data = \Piwik\API\Request::processRequest('Actions.getPageUrls', array(
			'idSite'    => $idSite,
			'period'    => $period,
			'date'      => $date,
			'segment'   => $segment,
		));
		$data->applyQueuedFilters();

		$result = $data->getEmptyClone($keepFilters = false);

//        foreach ($data->getRows() as $visitRow) {
//            $browserName = $visitRow->getColumn('label');
//
//            $result->addRowFromSimpleArray(array(
//                'label'             => $browserName,
//                'nb_uniq_visitors'  => $visitRow->getColumn('nb_uniq_visitors')
//            ));
//        }

		return $result;
	}

	public function overviewGetUserSpeed( $lastMinutes, $metrics , $refreshAfterXSecs )
	{
		$idSite     = Common::getRequestVar('idSite', 1);

		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
		$nameCdn    = explode("//",$nameCdn)[1];

		$now = time();
		$before_3mins = $now - ($lastMinutes * 60);
		$date_param = date("Y-m-d H:i:s", $before_3mins).",".date("Y-m-d H:i:s", $before_3mins);
		$params = array(
			'name'      => $nameCdn,
			'date'      => "$date_param",
			'period'    => 'range',
			'unit'      => 'minute', // range 1 minute
			'type'      => 'avg_speed',
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
						$graphData[ $valueByTime['name'] ][ $valueOfTypeRequest['type'] ] = (int)$valueByTime['value'];
					}
				}
			}
		}

		(int)$userSpeed  = current(current($graphData));
		$formatter = new Formatter();

		return array(
			'user_speed'        => $formatter->getPrettySizeFromBytes((int)$userSpeed, '', 2),
			'refreshAfterXSecs' => 5,
			'metrics'           => 'avg_speed',
			'lastMinutes'       => $lastMinutes
		);
	}





	public function getTraffps($idSite, $lastMinutes, $metric) {
		$rollups = \Piwik\API\Request::processRequest('RollUpReporting.getRollUps', array(
                ));

                foreach( $rollups as $rollup) {
                        if($idSite == $rollup['idsite']) {
                                $data = $this->getTraffpsForRollup($rollup['sourceIdSites'], $lastMinutes, $metric);
                        }
                }
                if(!$data) {
                        $data = $this->getTraffpsForSingleSite($idSite, $lastMinutes, $metric);
                }

                $formatter = new Formatter();
                $data = $formatter->getPrettySizeFromBytes($data);
                $split = explode(" ", $data);
                $graphData['traffic_ps']    = $split[0];
                $graphData['unit']         = $split[1];

                return $graphData;
	}

	protected function getTraffpsForRollup($idSites, $lastMinutes, $metric) {
		$total = 0;
                foreach($idSites as $idSite) {
                        $value = $this->getTraffpsForSingleSite($idSite, $lastMinutes, $metric);
                        if(!$value) {
                                continue;
                        }
                        $total += $value;
                }
                return $total;
	}


	/**
	 * @return mixed
	 */
	protected function getTraffpsForSingleSite($idSite, $lastMinutes, $metric) {
		$now = date("Y-m-d H:i:s");
		$time = strtotime($now) - ($lastMinutes * 60);
		$lastTime = date("Y-m-d H:i:s", $time);

		if(!$idSite) {
			$idSite = Common::getRequestVar('idSite', 1);
		}
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
		$nameCdn    = explode("//",$nameCdn)[1];

		$date_param = $lastTime.",".$lastTime;
		$params = array(
			'name'      => $nameCdn,
			'date'      => "$date_param",
			'period'    => 'range',
			'unit'      => 'minute', // range 2 minute
			'type'      => $metric ? $metric : 'traffic_ps',
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);

		$format = new Formatter();
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
						//$graphData[ $valueOfTypeRequest['type'] ] = $format->getPrettySizeFromBytes((int)$valueByTime['value']);
						$value = (int)$valueByTime['value'];
					}
				}
			}
		}
		//$split = explode(" ", $graphData['traffic_ps']);
		//$graphData['traffic_ps']    = $split[0];
		//$graphData['unit']          = $split[1];

		//return $graphData;
		return $value;
	}

	protected function checkRollUp($idSite) {

	}

	public function getAvgDl($idSite, $lastMinutes, $metric) {
		$rollups = \Piwik\API\Request::processRequest('RollUpReporting.getRollUps', array(
		));
		
		foreach( $rollups as $rollup) {
			if($idSite == $rollup['idsite']) {
				$data = $this->getAvgDlForRollup($rollup['sourceIdSites'], $lastMinutes, $metric);
			}
		}
		if(!$data) {
			$data = $this->getAvgDlForSingleSite($idSite, $lastMinutes, $metric);
		}

		$formatter = new Formatter();
		$data = $formatter->getPrettySizeFromBytes($data);
		$split = explode(" ", $data);
		$graphData['avg_speed']    = $split[0];
		$graphData['unit']         = $split[1];

		return $graphData;
	}

	protected function getAvgDlForRollup($idSites, $lastMinutes, $metric) {
		//var_dump($idSites);
		//$total['avg_speed'] = 0;
		//$total['unit'] = 'K';
		$total = 0;
		foreach($idSites as $idSite) {
			$value = $this->getAvgDlForSingleSite($idSite, $lastMinutes, $metric);
			if(!$value) {
				continue;
			}		
			$total += $value;
		}
		return $total;
	}

	protected function getAvgDlForSingleSite($idSite, $lastMinutes, $metric) {
		$now = date("Y-m-d H:i:s");
		$time = strtotime($now) - ($lastMinutes * 60);
		$lastTime = date("Y-m-d H:i:s", $time);

		if(!$idSite) {
			$idSite = Common::getRequestVar('idSite', 1);
		}
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getMainUrl();
		$nameCdn    = explode("//",$nameCdn)[1];

		$date_param = $lastTime.",".$lastTime;
		$params = array(
			'name'      => $nameCdn,
			'date'      => "$date_param",
			'period'    => 'range',
			'unit'      => 'minute', // range 2 minute
			'type'      => $metric ? $metric : 'avg_speed',
		);

		$dataCustomer = $this->apiGetCdnDataMk($params);
		$dataCustomer = json_decode($dataCustomer, true);

		$format = new Formatter();
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
						//$graphData[ $valueOfTypeRequest['type'] ] = $format->getPrettySizeFromBytes((int)$valueByTime['value']);
						$value = (int)$valueByTime['value'];
					}
				}
			}
		}
		//$split = explode(" ", $graphData['avg_speed']);
		//$graphData['avg_speed']    = $split[0];
		//$graphData['unit']         = $split[1];

		//return $graphData;
		return $value;
	}

	private function apiGetCdnDataMk( $data )
	{
		$ipAddressSetting = new SystemSettings('qosApiAddress');
		$url = $ipAddressSetting->qosApiAddress->getValue();

		$query = "?name=".$data['name']."&date=".$data['date']."&period=".$data['period']."&unit=".$data['unit']."&type=".$data['type'];

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
