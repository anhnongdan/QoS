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
use Piwik\Plugin\ViewDataTable;

class GetBrowsers extends Base
{
    var $metrics;
    protected function init()
    {
        $this->metrics = array('nb_uniq_visitors');
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->title = $this->name;
        $view->config->show_search = true;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', 'QoS_Browsers');

        $view->config->columns_to_display = array_merge(array('label'), $this->metrics);

    }
}
