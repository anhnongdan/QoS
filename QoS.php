<?php


namespace Piwik\Plugins\QoS;

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
        $files[] = 'plugins/QoS/javascripts/qos.js';
    }
}