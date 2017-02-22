(function($) {
	$.fn.extend({
		gaugeWidget: function(userSettings) {
			var settings = {
				// minimal time in microseconds to wait between updates
				interval: 5000,
				// maximum time to wait between requests
				maxInterval: 10000,
				// url params to use for data request
				dataUrlParams: null,
				// valueName
				valueName: null,
				// maxValueName
				maxValueName: null,
				// label to show beneath widget
				label: null
			};

			var currentInterval, updateInterval, gaugeWidget, gaugePlot;
			function scheduleAnotherRequest() {
				setTimeout(function () { update(); }, currentInterval);
			}
			function update() {
				var ajaxRequest = new ajaxHelper();
				ajaxRequest.addParams(settings.dataUrlParams, 'GET');
				ajaxRequest.setFormat('json');
				ajaxRequest.setCallback(function(r) {
					var current = r[0][settings.valueName];
					var max     = r[0][settings.maxValueName];
					var unit    = r[0][settings.unit];

					drawGaugePlot(current, max, unit);
					// check new interval doesn't reach the defined maximum
					if(settings.maxInterval < currentInterval) {
						currentInterval = settings.maxInterval;
					}
					window.clearTimeout(updateInterval);
					scheduleAnotherRequest();
					// if($(gaugeWidget).closest('body').length) {
					// 	updateInterval = window.setTimeout(update, currentInterval);
					// }
				});
				ajaxRequest.send(true);
			}

			function drawGaugePlot(current, max, unit) {
				var intervals = [5,max];
				if(gaugePlot) {
					// gaugePlot.destroy();
				}
				console.log(current+' '+unit+settings.label, max);
				if(!gaugePlot) {
					gaugePlot = $.jqplot(gaugeWidget.id,[[current]],{
						seriesDefaults: {
							renderer: $.jqplot.MeterGaugeRenderer,
							rendererOptions: {
								label: current+' '+unit+settings.label,
								labelPosition: 'inside',
								intervalOuterRadius: 85,
								intervals: intervals,
								intervalColors:['#cc6666', '#66cc66'],
								min: 0,
								max: max
							}
						}
					});
				}
			}
			/**
			 * Triggers an update for the widget
			 *
			 */
			this.update = function() {
				update();
			};

			return this.each(function() {
				settings = jQuery.extend(settings, userSettings);
				if(!settings.dataUrlParams) {
					console && console.error('error: dataUrlParams needs to be defined in settings.');
					return;
				}
				if(!settings.label) {
					console && console.error('error: label needs to be defined in settings.');
					return;
				}
				gaugeWidget = this;
				currentInterval = settings.interval;
				updateInterval = window.setTimeout(update, currentInterval);
				// start update
				update();
			});
		}
	});
})(jQuery);