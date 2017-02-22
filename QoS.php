<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\QoS;

require_once PIWIK_INCLUDE_PATH . '/plugins/QoS/functions.php';

class QoS extends \Piwik\Plugin
{
	public function registerEvents()
	{
		return array(
			'AssetManager.getJavaScriptFiles'   => 'getJavaScriptFiles',
			'AssetManager.getStylesheetFiles'   => 'getStylesheetFiles',
		);
	}

	public function getStylesheetFiles(&$stylesheets)
	{
		$stylesheets[] = "plugins/QoS/stylesheets/qos.css";
	}

	public function getJavaScriptFiles(&$files)
	{
//		$files[] = 'plugins/QoS/javascripts/jquery.jqplot.js';
//		$files[] = 'plugins/QoS/javascripts/jqplot.meterGaugeRenderer.js';
//		$files[] = 'plugins/QoS/javascripts/qosMeterGauge.js';
		$files[] = 'plugins/QoS/javascripts/qos.js';
	}

    public function extendVisitorDetails(&$visitor, $details)
    {
        $instance = new Visitor($details);
        $visitor['browser']                  = $instance->getBrowser();
        $visitor['browserName']              = $instance->getBrowserName();
        $visitor['browserIcon']              = $instance->getBrowserIcon();
        $visitor['browserCode']              = $instance->getBrowserCode();
        $visitor['browserVersion']           = $instance->getBrowserVersion();
    }
}