<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\QoS;
use Piwik\Piwik;
use Piwik\Settings\SystemSetting;

/**
 * Defines Settings.
 */
class Settings extends \Piwik\Plugin\Settings
{

	/** @var QoSSetting */
	public $qosSettings;

	/** @var overview */
	public $overview;

	/** @var speedDownload */
	public $speedDownload;

	/** @var speedDownloadIsp */
	public $speedDownloadIsp;

	/** @var httpCode */
	public $httpCode;

	/** @var cacheHit */
	public $cacheHit;

	/** @var isp */
	public $isp;

	/** @var country */
	public $country;

	protected function init()
	{
		$this->setIntroduction(Piwik::translate('QoS_SettingIntro'));

		$this->createQoSSetting();
		$this->createOverviewSetting();
		$this->createTrafficSetting();
		$this->createTotalSpeedDownloadSetting();
		$this->createIspSpeedDownloadSetting();
		$this->createHttpCodeSetting();
		$this->createCacheHitSetting();
		$this->createIspSetting();
		$this->createCountrySetting();
	}

	private function createQoSSetting()
	{
		$this->qosSettings        = new SystemSetting('qosSettings', Piwik::translate('QoS_SettingLabel') );
		$this->qosSettings->type  = static::TYPE_STRING;
		$this->qosSettings->uiControlType = static::CONTROL_TEXT;
		$this->qosSettings->description     = Piwik::translate('QoS_SettingDescription');
		$this->qosSettings->inlineHelp      = Piwik::translate('QoS_ServerSettingHelp');
		$this->qosSettings->defaultValue    = false;

		$this->addSetting($this->qosSettings);
	}

	private function createOverviewSetting()
	{
		$this->overview        = new SystemSetting('overview', 'Metrics of Traffic');
		$this->overview->type  = static::TYPE_ARRAY;
		$this->overview->uiControlType = static::CONTROL_MULTI_SELECT;
		// $this->overview->availableValues  = array();
		$this->overview->description   = 'The list metrics show on Overview';
		$this->overview->defaultValue  = array(
			'traffic_ps'    => 'traffic_ps',
			'avg_speed'     => 'avg_speed',
			'body_bytes_sent' => 'body_bytes_sent'
		);
		$this->overview->readableByCurrentUser = true;

		$this->addSetting($this->overview);
	}

	private function createTrafficSetting()
	{
		$this->traffic        = new SystemSetting('traffic', 'Metrics of Traffic');
		$this->traffic->type  = static::TYPE_ARRAY;
		$this->traffic->uiControlType = static::CONTROL_MULTI_SELECT;
		// $this->traffic->availableValues  = array();
		$this->traffic->description   = 'The value will be only displayed in the following http code 2xx in only isp';
		$this->traffic->defaultValue  = array(
			'isp_traffic_ps_total',
			'isp_traffic_ps_vnpt',
			'isp_traffic_ps_vinaphone',
			'isp_traffic_ps_viettel',
			'isp_traffic_ps_fpt',
			'isp_traffic_ps_mobiphone'
		);
		$this->traffic->readableByCurrentUser = true;

		$this->addSetting($this->traffic);
	}

	private function createHttpCodeSetting()
	{
		$this->httpCode        = new SystemSetting('httpCode', 'Metrics Http Code');
		$this->httpCode->type  = static::TYPE_ARRAY;
		$this->httpCode->uiControlType = static::CONTROL_MULTI_SELECT;
		$this->httpCode->availableValues  = array('request_count_200' => 'Http code 200', 'request_count_204' => 'Http code 200', 'request_count_206' => 'Http code 206');
		$this->httpCode->description   = 'The value will be only displayed in the following http code 2xx';
		$this->httpCode->defaultValue  = array(
			'2xx'   => array('request_count_200','request_count_204','request_count_206'),
			'3xx'   => array('request_count_301','request_count_302','request_count_304'),
			'4xx'   => array('request_count_400','request_count_404'),
			'5xx'   => array('request_count_500','request_count_502','request_count_503','request_count_504')
		);
		$this->httpCode->readableByCurrentUser = true;

		$this->addSetting($this->httpCode);
	}

	private function createCacheHitSetting()
	{
		$this->cacheHit        = new SystemSetting('cacheHit', 'Metrics Cache Hit');
		$this->cacheHit->type  = static::TYPE_ARRAY;
		$this->cacheHit->uiControlType = static::CONTROL_MULTI_SELECT;
//		 $this->cacheHit->availableValues  = array();
		$this->cacheHit->description   = 'The value will be only displayed in the following http code 2xx in only isp';
		$this->cacheHit->defaultValue  = array(
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
		$this->cacheHit->readableByCurrentUser = true;

		$this->addSetting($this->cacheHit);
	}

	private function createTotalSpeedDownloadSetting()
	{
		$this->totalSpeedDownload        = new SystemSetting('totalSpeedDownload', 'Metrics Average Speed');
		$this->totalSpeedDownload->type  = static::TYPE_ARRAY;
		$this->totalSpeedDownload->uiControlType = static::CONTROL_MULTI_SELECT;
		$this->totalSpeedDownload->description   = 'The value will be only displayed in the following speed download';
		$this->totalSpeedDownload->defaultValue  = array(
			'total'     => array(
				'isp_avg_speed_vnpt','isp_avg_speed_vinaphone',
				'isp_avg_speed_viettel',
				'isp_avg_speed_fpt',
				'isp_avg_speed_viettel_mobiphone'
			),
		);
		$this->totalSpeedDownload->readableByCurrentUser = true;

		$this->addSetting($this->totalSpeedDownload);
	}

	private function createIspSpeedDownloadSetting()
	{
		$this->ispSpeedDownload        = new SystemSetting('ispSpeedDownload', 'Metrics of Traffic');
		$this->ispSpeedDownload->type  = static::TYPE_ARRAY;
		$this->ispSpeedDownload->uiControlType = static::CONTROL_MULTI_SELECT;
		$this->ispSpeedDownload->description   = 'The value will be only displayed in the following http code 2xx in only isp';
		$this->ispSpeedDownload->defaultValue  = array(
			'total'     => array ('isp_avg_speed_total'),
			'vnpt'      => array ('isp_avg_speed_vnpt','isp_avg_speed_vinaphone'),
			'viettel'   => array ('isp_avg_speed_viettel'),
			'fpt'       => array ('isp_avg_speed_fpt'),
			'mobiphone' => array ('isp_avg_speed_mobiphone'),
		);
		$this->ispSpeedDownload->readableByCurrentUser = true;

		$this->addSetting($this->ispSpeedDownload);
	}

	private function createIspSetting()
	{
		$this->isp        = new SystemSetting('isp', 'Metrics of Isp');
		$this->isp->type  = static::TYPE_ARRAY;
		$this->isp->uiControlType = static::CONTROL_MULTI_SELECT;
		// $this->cacheHit->availableValues  = array();
		$this->isp->description   = 'The value will be only displayed in the following http code 2xx in only isp';
		$this->isp->defaultValue  = array(
			'vnpt'          => array ('isp_request_count_200_vnpt','isp_request_count_206_vnpt'),
			'vinaphone'     => array ('isp_request_count_200_vinaphone','isp_request_count_206_vinaphone'),
			'viettel'   => array ('isp_request_count_200_viettel','isp_request_count_206_viettel'),
			'fpt'       => array ('isp_request_count_200_fpt','isp_request_count_206_fpt'),
			'mobiphone' => array ('isp_request_count_200_mobiphone','isp_request_count_206_mobiphone'),
		);
		$this->isp->readableByCurrentUser = true;

		$this->addSetting($this->isp);
	}

	private function createCountrySetting()
	{
		$this->country        = new SystemSetting('country', 'Metrics of Isp');
		$this->country->type  = static::TYPE_ARRAY;
		$this->country->uiControlType = static::CONTROL_MULTI_SELECT;
		// $this->cacheHit->availableValues  = array();
		$this->country->description   = 'The value will be only displayed in the following speed download';
		$this->country->defaultValue  = array('country_request_count_200_VN','country_request_count_200_US','country_request_count_200_CN');

		$this->country->readableByCurrentUser = true;

		$this->addSetting($this->country);
	}
}
