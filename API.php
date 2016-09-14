<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\QoS;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Site;

use Piwik\API\Request;


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
    private $config;
    private $overview;
    private $bandwidth;
    private $userSpeed;
    private $cacheHit;
    private $httpCode;
    private $isp;
    private $country;

    function __construct()
    {
        $this->setHttpCode();
        $this->setConfig();
    }

    public function getHttpCode() {
        return $this->httpCode;
    }

    public function getConfig() {
        return $this->config;
    }

    private function setHttpCode()
    {
        $httpCode = new Settings('QoS');
        $this->httpCode = $httpCode->httpCode->getValue();
    }

    private function setConfig()
    {
        // code later
    }
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
//		ksort($bandwidthData);
		$graphData = array_slice($bandwidthData, -24, 24, true);
		$tmp = array();
		foreach ( $graphData as $keyTime => $valueByTime )
		{
//			$key = explode(" ", $keyTime);
//			$tmp[ $key[1]."h" ] = $valueByTime['avg_speed'];
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
		$nameCdn    = $cdnObj->getName();

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

	public function buildDataIspGraph()
	{
		$columns = 'isp_request_count_200_mobiphone,isp_request_count_200_vinaphone,isp_request_count_200_fpt,isp_request_count_200_viettel,isp_request_count_200_vnpt';

		$idSite = Common::getRequestVar('idSite', 1);
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getName();

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
		$nameCdn    = $cdnObj->getName();

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

	public function overViewSppedGraph($idSite, $metric)
	{
	    if(!$idSite) {
            $idSite = Common::getRequestVar('idSite', 1);
        }

		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getName();

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

		return current(current($graphData));
	}

	public function overViewCacheHitGraph($idSite, $metric)
	{
	    if(!$idSite) {
            $idSite = Common::getRequestVar('idSite', 1);
        }

		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getName();

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

		return current(current($graphData));
	}

	public function getEvolutionOverview($idSite, $date, $period, $columns = false)
	{
		$cdnObj     = new Site($idSite);
		$nameCdn    = $cdnObj->getName();

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
echo "<pre>";
    var_dump($columns);
echo "</pre>";
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

    public function getDevelopmentAreaApi($idSite, $period, $date, $segment = false, $columns = false)
    {
        $cdnObj     = new Site($idSite);
        $nameCdn    = $cdnObj->getName();

        $typePeriod = $this->countStepPeriod($period);
        $dates      = explode(",", $date);
        echo "<pre>";
            var_dump($columns);
        echo "</pre>";

        if (!$columns) {
            $columns = Common::getRequestVar('columns', false);
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

    public function get($idSite, $period, $date, $segment = false, $columns = false)
    {
        return DataTable::makeFromSimpleArray(array());
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
