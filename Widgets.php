<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\QoS;

class Widgets extends \Piwik\Plugin\Widgets
{
	protected $category = 'Live!';

	public function init()
	{
		$this->addWidget('QoS_RealTimeCDNThruput', 'widRealtimeThru', array('columns' => array('traffic_ps')));
		$this->addWidget('QoS_RealTimeAvgDLSpeed', 'widRealtimeAvgD', array('columns' => array('avg_speed')));
	}
}
