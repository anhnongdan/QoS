<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\QoS;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\View;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;


class Controller extends \Piwik\Plugin\Controller
{

	public function overview()
	{
		$view = new View('@QoS/overview');

		$this->setPeriodVariablesView($view);

		$view->graphHttpCode        = $this->overViewHttpCodeGraph( 'graphPie', array('request_count_200','request_count_204','request_count_206') );
		$view->graphOverviewBw      = $this->overViewBandwidthGraph( 'graphVerticalBar', array('traffic_ps') );
		$view->graphIsp             = $this->overViewIspGraph('graphPie', array('isp_request_count_200_mobiphone,isp_request_count_200_vinaphone,isp_request_count_200_fpt,isp_request_count_200_viettel,isp_request_count_200_vnpt'), array('isp_request_count_200_mobiphone,isp_request_count_200_vinaphone,isp_request_count_200_fpt,isp_request_count_200_viettel,isp_request_count_200_vnpt'));
		$view->graphCountry         = $this->overViewCountryGraph('graphPie', array('country_request_count_200_VN','country_request_count_200_US','country_request_count_200_CN'), array('country_request_count_200_VN','country_request_count_200_US','country_request_count_200_CN'));
		$view->graphCacheHit        = API::getInstance()->overViewCacheHitGraph($this->idSite, $metric = 'isp_request_count_200_viettel');
		$view->graphSpeed           = API::getInstance()->overViewSppedGraph($this->idSite, $metric = 'avg_speed');

		return $view->render();
	}

	public function bandwidth()
	{
		$view = new View('@QoS/bandwidth');
        $this->setGeneralVariablesView($view);
		$view->graphBandwidth   = $this->getEvolutionGraphBw(array(), array('traffic_ps'));

		return $view->render();
	}

	public function userSpeed()
	{
		$view = new View('@QoS/userspeed');
        $this->setGeneralVariablesView($view);
		$view->graphUserSpeed   = $this->getEvolutionGraph(array('avg_speed'), array('avg_speed'), 'getIndexGraph');

		return $view->render();
	}

	public function cacheHit()
	{
		$view = new View('@QoS/cacheHit');

		$view->graphCacheHitVnpt         = $this->getEvolutionGraph(array(), array('isp_request_count_200_vnpt','isp_request_count_206_vnpt'));
		$view->graphCacheHitVinaphone    = $this->getEvolutionGraph(array(), array('isp_request_count_200_vinaphone','isp_request_count_206_vinaphone'));
		$view->graphCacheHitViettel      = $this->getEvolutionGraph(array(), array('isp_request_count_200_fpt','isp_request_count_206_fpt'));
		$view->graphCacheHitFpt          = $this->getEvolutionGraph(array(), array('isp_request_count_200_viettel','isp_request_count_206_viettel'));
		$view->graphCacheHitMobifone     = $this->getEvolutionGraph(array(), array('isp_request_count_200_vnpt','isp_request_count_206_vnpt'));

		return $view->render();
	}

	public function httpCode()
	{
		$view = new View('@QoS/httpcode');

		// $this->setGeneralVariablesView($view);
		$this->setPeriodVariablesView($view);

		$view->graphErrorCode200    = $this->getEvolutionGraph(array(), array('request_count_200','request_count_204','request_count_206'), 'getIndexGraph');
		$view->graphErrorCode300    = $this->getEvolutionGraph(array(), array('request_count_301','request_count_302','request_count_304'), 'getIndexGraph');
		$view->graphErrorCode400    = $this->getEvolutionGraph(array(), array('request_count_400','request_count_404'), 'getIndexGraph');
		$view->graphErrorCode500    = $this->getEvolutionGraph(array(), array('request_count_500','request_count_502','request_count_503','request_count_504'), 'getIndexGraph');

		return $view->render();
	}

	public function isp()
	{
		$view = new View('@QoS/isp');

		// mobiphone, vinaphone, fpt, viettel, vnpt
		$view->graphIspVnpt         = $this->getEvolutionGraph(array('isp_request_count_200_vnpt'), array('isp_request_count_200_vnpt'));
		$view->graphIspVinaphone    = $this->getEvolutionGraph(array('isp_request_count_200_vinaphone'), array('isp_request_count_200_vinaphone'));
		$view->graphIspViettel      = $this->getEvolutionGraph(array('isp_request_count_200_fpt'), array('isp_request_count_200_fpt'));
		$view->graphIspFpt          = $this->getEvolutionGraph(array('isp_request_count_200_viettel'), array('isp_request_count_200_viettel'));
		$view->graphIspMobifone     = $this->getEvolutionGraph(array('isp_request_count_200_vnpt'), array('isp_request_count_200_vnpt'));

		return $view->render();
	}

	public function country()
	{
		$view = new View('@QoS/country');

		$view->graphCountry         = $this->getEvolutionGraph(array('country_request_count_200_VN','country_request_count_200_US','country_request_count_200_CN'), array('country_request_count_200_VN'));

		return $view->render();
	}

	public function development()
	{
		$view = new View('@QoS/development');

		$view->graphDevelopment    = $this->getDevelopmentArea(array(), array('request_count_200','request_count_204','request_count_206'), 'getDevelopmentAreaApi');

		return $view->render();
	}

	public function getDevelopmentArea(array $columns = array(), array $defaultColumns = array(), $apiMethod)
	{
		if (empty($columns)) {
			$columns = Common::getRequestVar('columns', false);
			if (false !== $columns) {
				$columns = Piwik::getArrayFromApiParameter($columns);
			}
		}

		$selectableColumns = $defaultColumns;

		$view = $this->getLastUnitGraphAcrossPlugins($this->pluginName, __FUNCTION__, $columns, $selectableColumns, '', 'QoS.'.$apiMethod);

		$view->config->selectable_columns = $selectableColumns;
		$view->config->columns_to_display = $defaultColumns;
		$view->config->enable_sort          = false;
		$view->config->max_graph_elements   = 30;
		$view->requestConfig->filter_sort_column = 'label';
		$view->requestConfig->filter_sort_order  = 'asc';
		$view->requestConfig->disable_generic_filters=true;

	   // if (empty($view->config->columns_to_display) && !empty($defaultColumns)) {
	   //     $view->config->columns_to_display = $defaultColumns;
	   // }

		return $this->renderView($view);
	}

	public function overViewBandwidthGraph($type = 'graphVerticalBar', $metrics = array())
	{
		$view = ViewDataTableFactory::build( $type, 'QoS.buildDataBwGraph', 'QoS.overViewBandwidthGraph', false );

		$view->config->y_axis_unit  = ' bit';
		$view->config->show_footer  = true;
		$view->config->translations['value'] = Piwik::translate("QoS_Bandwidth");
		$view->config->selectable_columns   = array("value");
		$view->config->max_graph_elements   = 24;
		$view->requestConfig->filter_sort_column = 'label';
		$view->requestConfig->filter_sort_order  = 'asc';

		return $view->render();
	}

	public function overViewHttpCodeGraph($type = 'graphPie', $metrics = array())
	{
		$view = ViewDataTableFactory::build( $type, 'QoS.buildDataHttpCodeGraph', 'QoS.overViewHttpCodeGraph', false );

		$view->config->columns_to_display       = array('value');
		$view->config->translations['value']    = Piwik::translate("QoS_The_percentage_of_http_code_2xx");
		$view->config->show_footer_icons        = false;
		$view->config->selectable_columns       = array("value");
		$view->config->max_graph_elements       = 10;

		return $view->render();
	}

	public function overViewIspGraph($type = 'graphPie', $metrics = array())
	{
		$view = ViewDataTableFactory::build( $type, 'QoS.buildDataIspGraph', 'QoS.overViewIspGraph', false );

		$view->config->columns_to_display       = array('value');
		$view->config->translations['value']    = Piwik::translate("QoS_The_percentage_of_list_isp");
		$view->config->show_footer_icons        = false;
		$view->config->selectable_columns       = array("value");
		$view->config->max_graph_elements       = 10;

		return $view->render();
	}

	public function overViewCountryGraph($type = 'graphPie', $metrics = array())
	{
		$view = ViewDataTableFactory::build( $type, 'QoS.buildDataCountryGraph', 'QoS.overViewCountryGraph', false );

		$view->config->columns_to_display       = array('value');
		$view->config->translations['value']    = Piwik::translate("QoS_The_percentage_of_list_isp");
		$view->config->show_footer_icons        = false;
		$view->config->selectable_columns       = array("value");
		$view->config->max_graph_elements       = 10;

		return $view->render();
	}

	public function getIndexGraph()
	{
		return $this->getEvolutionGraph(array(), array(), __FUNCTION__);
	}

	public function getEvolutionGraph(array $columns = array(), array $defaultColumns = array())
	{
		if (empty($columns)) {
			$columns = Common::getRequestVar('columns', false);
			if (false !== $columns) {
				$columns = Piwik::getArrayFromApiParameter($columns);
			}
		}

		$selectableColumns = $defaultColumns;
echo "<pre>";
    var_dump($defaultColumns);
echo "</pre>";
		$view = $this->getLastUnitGraphAcrossPlugins($this->pluginName, __FUNCTION__, $columns, $selectableColumns, '', 'QoS.getGraphEvolution');

		// $view->config->setDefaultColumnsToDisplay($selectableColumns);
		$view->config->enable_sort          = false;
		$view->config->max_graph_elements   = 30;
		$view->requestConfig->filter_sort_column = 'label';
		$view->requestConfig->filter_sort_order  = 'asc';
		$view->requestConfig->disable_generic_filters=true;

		if (empty($view->config->columns_to_display) && !empty($defaultColumns)) {
			$view->config->columns_to_display = $defaultColumns;
		}

		return $this->renderView($view);
	}

    public function getEvolutionGraphBw(array $columns = array(), array $defaultColumns = array())
    {
        if (empty($columns)) {
            $columns = Common::getRequestVar('columns', false);
            if (false !== $columns) {
                $columns = Piwik::getArrayFromApiParameter($columns);
            }
        }

        $selectableColumns = $defaultColumns;

        $view = $this->getLastUnitGraphAcrossPlugins($this->pluginName, __FUNCTION__, $columns, $selectableColumns, '', 'QoS.getGraphEvolutionBw');

        // $view->config->setDefaultColumnsToDisplay($selectableColumns);
        $view->config->enable_sort          = false;
        $view->config->max_graph_elements   = 30;
        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order  = 'asc';
        $view->requestConfig->disable_generic_filters=true;

        if (empty($view->config->columns_to_display) && !empty($defaultColumns)) {
            $view->config->columns_to_display = $defaultColumns;
        }

        return $this->renderView($view);
    }
}
