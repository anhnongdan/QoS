<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\QoS\Widgets;

use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;
use Piwik\View;

class AvgDWidgets extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('Live!');
        $config->setSubcategoryId('QoS_RealTimeAvgDLSpeed');
        $config->setName('QoS_RealTimeAvgDLSpeed');
        $config->setOrder(10);
    }

    public function render()
    {
        return $this->renderTemplate('widRealtimeAvgD', array('columns' => array('avg_speed')));
    }

//	public function init()
//	{
//		$this->addWidget('QoS_RealTimeCDNThruput', 'widRealtimeThru', array('columns' => array('traffic_ps')));
//		$this->addWidget('QoS_RealTimeAvgDLSpeed', 'widRealtimeAvgD', array('columns' => array('avg_speed')));
//	}
}
