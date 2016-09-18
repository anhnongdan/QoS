$(document).ready(function () {

	setInterval(function () {

		// get the root element for our report
		var $dataTableRoot = $('.dataTable[data-report="QoS.overViewBandwidthGraph"]');

		// in the UI, the root element of a report has a JavaScript object associated to it.
		// we can use this object to reload the report.
		var dataTableInstance = $dataTableRoot.data('uiControlObject');

		// we want the table to be completely reset, so we'll reset some
		// query parameters then reload the report
		dataTableInstance.resetAllFilters();
		dataTableInstance.reloadAjaxDataTable();

	}, 3600 * 1000);

	setInterval(function () {

		// get the root element for our report
		var $dataTableRoot = $('.dataTable[data-report="QoS.overViewHttpCodeGraph"]');

		// in the UI, the root element of a report has a JavaScript object associated to it.
		// we can use this object to reload the report.
		var dataTableInstance = $dataTableRoot.data('uiControlObject');

		// we want the table to be completely reset, so we'll reset some
		// query parameters then reload the report
		dataTableInstance.resetAllFilters();
		dataTableInstance.reloadAjaxDataTable();

	}, 5 * 1000);

	setInterval(function () {

		// get the root element for our report
		var $dataTableRoot = $('.dataTable[data-report="QoS.overViewIspGraph"]');

		// in the UI, the root element of a report has a JavaScript object associated to it.
		// we can use this object to reload the report.
		var dataTableInstance = $dataTableRoot.data('uiControlObject');

		// we want the table to be completely reset, so we'll reset some
		// query parameters then reload the report
		dataTableInstance.resetAllFilters();
		dataTableInstance.reloadAjaxDataTable();

	}, 5 * 1000);

	setInterval(function () {

		// get the root element for our report
		var $dataTableRoot = $('.dataTable[data-report="QoS.overViewCountryGraph"]');

		// in the UI, the root element of a report has a JavaScript object associated to it.
		// we can use this object to reload the report.
		var dataTableInstance = $dataTableRoot.data('uiControlObject');

		// we want the table to be completely reset, so we'll reset some
		// query parameters then reload the report
		dataTableInstance.resetAllFilters();
		dataTableInstance.reloadAjaxDataTable();

	}, 5 * 1000);

});