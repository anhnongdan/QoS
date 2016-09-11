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

		$view->graphIsp             = $this->overViewIspGraph('graphPie', array('mobiphone,vinaphone,fpt,viettel,vnpt'), array('mobiphone,vinaphone,fpt,viettel,vnpt'));
		$view->graphCountry         = $this->overViewCountryGraph('graphPie', array('country_request_count_200_VN','country_request_count_200_US','country_request_count_200_CN'), array('request_count_301'));
		$view->graphErrorCode300    = $this->getEvolutionGraph(array('request_count_400','request_count_404'), array('request_count_400'));
		$view->graphErrorCode500    = $this->getEvolutionGraph(array('request_count_500','request_count_502','request_count_503','request_count_504'), array('request_count_500'));
		$this->setSparklinesAndNumbers($view);

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
		$view->graphIspVnpt         = $this->getEvolutionGraph(array('vnpt'), array('vnpt'));
		$view->graphIspVinaphone    = $this->getEvolutionGraph(array('vinaphone'), array('vinaphone'));
		$view->graphIspViettel      = $this->getEvolutionGraph(array('fpt'), array('fpt'));
		$view->graphIspFpt          = $this->getEvolutionGraph(array('viettel'), array('viettel'));
		$view->graphIspMobifone     = $this->getEvolutionGraph(array('vnpt'), array('vnpt'));

		return $view->render();
	}

	public function country()
	{
		$view = new View('@QoS/country');

		$view->graphCountry         = $this->getEvolutionGraph(array('country_request_count_200_VN'), array('country_request_count_200_VN'));

		return $view->render();
	}

	public function overViewBandwidthGraph( $type = 'graphVerticalBar', $metrics = array())
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

	public function overViewHttpCodeGraph( $type = 'graphPie', $metrics = array())
	{
		$view = ViewDataTableFactory::build( $type, 'QoS.buildDataHttpCodeGraph', 'QoS.overViewHttpCodeGraph', false );

		$view->config->columns_to_display       = array('value');
		$view->config->translations['value']    = Piwik::translate("QoS_The_percentage_of_http_code_2xx");
		$view->config->show_footer_icons        = false;
		$view->config->selectable_columns       = array("value");
		$view->config->max_graph_elements       = 10;

		return $view->render();
	}

	public function overViewIspGraph( $type = 'graphPie', $metrics = array())
	{
		$view = ViewDataTableFactory::build( $type, 'QoS.buildDataIspGraph', 'QoS.overViewIspGraph', false );

		$view->config->columns_to_display       = array('value');
		$view->config->translations['value']    = Piwik::translate("QoS_The_percentage_of_list_isp");
		$view->config->show_footer_icons        = false;
		$view->config->selectable_columns       = array("value");
		$view->config->max_graph_elements       = 10;

		return $view->render();
	}

	public function overViewCountryGraph( $type = 'graphPie', $metrics = array())
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

//	public function overViewCountryGraph(array $columns = array(), array $defaultColumns = array())
//	{
//		if (empty($columns))
//		{
//			$columns = Common::getRequestVar('columns', false);
//			if (false !== $columns)
//			{
//				$columns = Piwik::getArrayFromApiParameter($columns);
//			}
//		}
//
//		$selectableColumns = $columns;
//
//		$view = $this->getLastUnitGraphAcrossPlugins($this->pluginName, __FUNCTION__, $columns, $selectableColumns, 'Documentation', 'QoS.getEvolutionOverview');
//
//		$view->config->enable_sort          = false;
//		$view->config->max_graph_elements   = 30;
//		$view->requestConfig->filter_sort_column = 'label';
//		$view->requestConfig->filter_sort_order  = 'asc';
//
//		if (empty($view->config->columns_to_display) && !empty($defaultColumns)) {
//			$view->config->columns_to_display = $defaultColumns;
//		}
//
//		return $this->renderView($view);
//	}

//	public function httpCode()
//	{
//		$view = new View('@QoS/httpcode');
//
//		$view->dataTableCounterRequest = $this->renderReport(__FUNCTION__);
//
//		$view->config->translations['value'] = 'QoS_Count';
//		$view->config->translations['label'] = 'QoS_Time';
//		$view->requestConfig->filter_sort_column    = 'label';
//		$view->requestConfig->filter_sort_order     = 'asc';
//		$view->requestConfig->filter_limit = 20;
//		$view->config->columns_to_display  = array('label', 'value');
//		$view->config->show_exclude_low_population = false;
//		$view->config->show_table_all_columns = false;
//		$view->config->disable_row_evolution  = true;
//		$view->config->max_graph_elements = 30;
//		$view->config->metrics_documentation = array('value' => 'Documentation');
//
//		return $view->render();
//	}

	public function setSparklinesAndNumbers($view)
	{
		$view->urlSparklineAverageRq200 	= $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('request_count_200')));
		$view->urlSparkline2                = $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('request_count_200', 'request_count_404', 'request_count_502')));

		$view->nbAverage200     = 2000000000;
	}
}
