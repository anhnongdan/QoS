<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\QoS\Reports;

use Piwik\Common;
use Piwik\Plugins\QoS\API;

abstract class Base extends \Piwik\Plugin\Report
{
    protected function init()
    {
        $this->category = 'QoS';
    }

    protected function addReportMetadataForEachModule(&$availableReports, $infos)
    {
        $idSite = $this->getIdSiteFromInfos($infos);
        $config  = $this->getModulesForIdSite($idSite);

        // Create reports
        $order = 50;

		$reports = array();
        foreach ($config as $domain => $servers) {
            foreach ($servers as $server => $names) {
                    foreach ($names as $name => $attributes) {
                            foreach ($attributes as $attribute => $value) {
                                if (API::getInstance()->endsWith($attribute, '.label'))
                                    $reports[] = array("name"=>$name, "title"=> $value);
                            }
                    }
            }
        }

        foreach ($reports as $report) {

            $this->name       = $report['title'];
            $this->parameters = array('name' => $report['name']);
            $this->order = $order;

            $availableReports[] = $this->buildReportMetadata($availableReports, $infos);

            $order = $order + 1;

        }

        $this->init();
    }

    protected function getIdSiteFromInfos($infos)
    {
        $idSites = $infos['idSites'];

        if (count($idSites) != 1) {
            return null;
        }

        $idSite = reset($idSites);

        return $idSite;
    }

    private function getModulesForIdSite($idSite)
    {
        if (empty($idSite)) {
            return array();
        }

        return API::getInstance()->getConfig();
    }
}
