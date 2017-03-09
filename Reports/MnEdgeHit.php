<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\QoS\Reports;

use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Cloud;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Pie;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class MnEdgeHit extends Base
{
    protected function init()
    {
        parent::init();

        $this->subcategoryId = 'QoS_EdgeHit';
        $this->order = 2;
    }

//    public function getDefaultTypeViewDataTable()
//    {
//        return PIE::ID;
//    }

//    public function configureView(ViewDataTable $view)
//    {
//        $view->config->addTranslation('value', 'times the diameter of Earth');
//
//        if ($view->isViewDataTableId(PIE::ID)) {
//
//            $view->config->columns_to_display = array('value');
//            $view->config->selectable_columns = array('value');
//            $view->config->show_footer_icons = false;
//            $view->config->max_graph_elements = 10;
//
//        } else if ($view->isViewDataTableId(Cloud::ID)) {
//
//            $view->config->columns_to_display = array('label', 'value');
//            $view->config->show_footer = false;
//
//        }
//    }
}
