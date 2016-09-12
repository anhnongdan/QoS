<?php

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
		$view->graphOverviewBw      = $this->overViewBandwidthGraph( 'graphVerticalBar', array('avg_speed') );
		$view->overviewCacheHit     = 76;

		$view->graphIsp             = $this->overViewIspGraph('graphPie', array('isp_request_count_200_mobiphone,isp_request_count_200_vinaphone,isp_request_count_200_fpt,isp_request_count_200_viettel,isp_request_count_200_vnpt'), array('isp_request_count_200_mobiphone,isp_request_count_200_vinaphone,isp_request_count_200_fpt,isp_request_count_200_viettel,isp_request_count_200_vnpt'));
		$view->graphCountry         = $this->overViewCountryGraph('graphPie', array('country_request_count_200_VN','country_request_count_200_US','country_request_count_200_CN'), array('country_request_count_200_VN','country_request_count_200_US','country_request_count_200_CN'));
		$view->graphCacheHit        = API::getInstance()->overViewCacheHitGraph($this->idSite, $metric = 'isp_request_count_200_viettel');
		$view->graphSpeed           = API::getInstance()->overViewSppedGraph($this->idSite, $metric = 'avg_speed');

		return $view->render();
	}

	public function bandwidth()
	{
		$view = new View('@QoS/bandwidth');

		return $view->render();
	}

	public function userSpeed()
	{
		$view = new View('@QoS/userSpeed');

		return $view->render();
	}

	public function cacheHit()
	{
		$view = new View('@QoS/cacheHit');

		return $view->render();
	}

	public function httpCode()
	{
		$view = new View('@QoS/httpcode');

		$view->graphErrorCode200    = $this->getEvolutionGraph(array('request_count_200','request_count_204','request_count_206'), array('request_count_200'));
		$view->graphErrorCode300    = $this->getEvolutionGraph(array('request_count_301','request_count_302','request_count_304'), array('request_count_301'));
		$view->graphErrorCode400    = $this->getEvolutionGraph(array('request_count_400','request_count_404'), array('request_count_400'));
		$view->graphErrorCode500    = $this->getEvolutionGraph(array('request_count_500','request_count_502','request_count_503','request_count_504'), array('request_count_500'));

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

	public function getEvolutionGraph(array $columns = array(), array $defaultColumns = array())
	{
		if (empty($columns))
		{
			$columns = Common::getRequestVar('columns', false);
			if (false !== $columns)
			{
				$columns = Piwik::getArrayFromApiParameter($columns);
			}
		}

		$selectableColumns = $columns;

		$view = $this->getLastUnitGraphAcrossPlugins($this->pluginName, __FUNCTION__, $columns, $selectableColumns, 'Documentation', 'QoS.getEvolutionOverview');

		$view->config->enable_sort          = false;
		$view->config->max_graph_elements   = 30;
		$view->requestConfig->filter_sort_column = 'label';
		$view->requestConfig->filter_sort_order  = 'asc';

		if (empty($view->config->columns_to_display) && !empty($defaultColumns)) {
			$view->config->columns_to_display = $defaultColumns;
		}

		return $this->renderView($view);
	}
}
