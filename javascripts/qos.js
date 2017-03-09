/* widgetthruput */
$(function() {
	var refreshWidget = function (element, refreshAfterXSecs) {
		// if the widget has been removed from the DOM, abort
		if (!element.length || !$.contains(document, element[0])) {
			return;
		}
		function scheduleAnotherRequest() {
			setTimeout(function () { refreshWidget(element, refreshAfterXSecs); }, refreshAfterXSecs * 1000);
		}
		if (Visibility.hidden()) {
			scheduleAnotherRequest();
			return;
		}
		var lastMinutes = $(element).attr('data-last-minutes') || 5;
		var ajaxRequest = new ajaxHelper();
		ajaxRequest.addParams({
			module: 'API',
			method: 'QoS.getTraffps',
			format: 'json',
			lastMinutes: lastMinutes,
			metric: 'traffic_ps',
			refreshAfterXSecs: 5
		}, 'get');
		ajaxRequest.setFormat('json');
		ajaxRequest.setCallback(function (data) {
			data = data[0];
			// set text and tooltip of visitors count metric
			var traff = data['traffic_ps'];
			var unit  = data['unit'];
			$('.realtime-thruput-counter', element)
				.attr('title', traff+' '+unit+'bps')
				.find('div').text(traff)
			$('.realtime-thruput-counter', element)
				.find('span').text(unit+'bps');
			$('.realtime-thruput-widget', element).attr('data-refreshafterxsecs', refreshAfterXSecs).attr('data-last-minutes', lastMinutes);

			scheduleAnotherRequest();
		});
		ajaxRequest.send(true);
	};

	var exports = require("piwik/QoS");
	exports.initRealtimeThruputWidget = function () {
		$('.realtime-thruput-widget').each(function() {
			var $this = $(this),
				refreshAfterXSecs = $this.attr('data-refreshAfterXSecs');
			if ($this.attr('data-inited')) {
				return;
			}
			$this.attr('data-inited', 1);
			setTimeout(function() { refreshWidget($this, refreshAfterXSecs ); }, refreshAfterXSecs * 1000);
		});
	};

	/* Avg Widget */
    var refreshWidgetAvg = function (element, refreshAfterXSecs) {
        // if the widget has been removed from the DOM, abort
        if (!element.length || !$.contains(document, element[0])) {
            return;
        }
        function scheduleAnotherRequest() {
            setTimeout(function () { refreshWidgetAvg(element, refreshAfterXSecs); }, refreshAfterXSecs * 1000);
        }
        if (Visibility.hidden()) {
            scheduleAnotherRequest();
            return;
        }
        var lastMinutes = $(element).attr('data-last-minutes') || 5;
        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({
            module: 'API',
            method: 'QoS.getAvgDl',
            format: 'json',
            lastMinutes: lastMinutes,
            metric: 'avg_speed',
            refreshAfterXSecs: 5
        }, 'get');
        ajaxRequest.setFormat('json');
        ajaxRequest.setCallback(function (data) {
            data = data[0];
            // set text and tooltip of visitors count metric
            var traff = data['avg_speed'];
            var unit  = data['unit'];
            $('.realtime-avg-counter', element)
                .attr('title', traff+' '+unit+'bps')
                .find('div').text(traff)
            $('.realtime-avg-counter', element)
                .find('span').text(unit+'bps');
            $('.realtime-avg-widget', element).attr('data-refreshafterxsecs', refreshAfterXSecs).attr('data-last-minutes', lastMinutes);

            scheduleAnotherRequest();
        });
        ajaxRequest.send(true);
    };

    var exports = require("piwik/QoS");
    exports.initRealtimeAvgWidget = function () {
        $('.realtime-avg-widget').each(function() {
            var $this = $(this),
                refreshAfterXSecs = $this.attr('data-refreshAfterXSecs');
            if ($this.attr('data-inited')) {
                return;
            }
            $this.attr('data-inited', 1);
            setTimeout(function() { refreshWidgetAvg($this, refreshAfterXSecs ); }, refreshAfterXSecs * 1000);
        });
    };
});