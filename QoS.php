<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\QoS;

class QoS extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles'
        );
    }

	public function getStylesheetFiles(&$stylesheets)
	{
		$stylesheets[] = "plugins/QoS/stylesheets/qos.css";
	}

    public function getJsFiles(&$jsFiles)
	{
        $jsFiles[] = 'plugins/QoS/javascripts/qos.js';
	}
}