<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\QoS\Reports;

use Piwik\Piwik;

class GetDevelopmentAreaApi extends \Piwik\Plugin\Report
{
    protected function init()
    {
        parent::init();
        $this->category      = 'QoS';
        $this->documentation = ''; // TODO
        $this->metrics       = array(
            'request_count_200',
            'request_count_204',
            'request_count_206'
        );

        $this->order = 1;
    }

    public function getMetrics()
    {
        $metrics = parent::getMetrics();

        return $metrics;
    }
}