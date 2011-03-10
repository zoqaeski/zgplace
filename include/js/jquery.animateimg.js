;(function($) {
	$.fn.animateImg = function(options) {
		var opts = $.extend({}, $.fn.animateImg.defaults, options);
		var image = $(this);
		return this.each(function() {

			//$.metadata.setType("html5");
			//var o = $.metadata ? $.extend({}, opts, $this.data()) : opts;
			//var o = $.extend({}, opts, $this.data());
			//console.log(o.intervals);

			// Get and parse options
			var o = $.extend({}, opts, image.dataset());
			var intervalList = o.intervals.split(',');
			var frameList = o.frames.split(',');

			// If there are more frames than intervals, repeat the
			// first interval and ignore the rest
			if(frameList.length != intervalList.length) {
				firstInterval = intervalList[0];
				intervalList = new Array(frameList.length);
				$.each(intervalList, function(index, value) {
					intervalList[index] = firstInterval;
				});
			}

			// Fix up intervals that are NaN by changing them to default interval value
			// This approach is better than killing the script, IMHO
			intervalList = $.map(intervalList, function(n, i) {
				var interval = parseInt(n);
				if(typeof interval != 'number' || isNaN(interval) || interval <= 0) 
					return $.fn.animateImg.defaults.intervals;
				return interval;
			});
			
			// Calculate total duration of the animation
			var total_interval = 0;
			$.each(intervalList, function(index, value) {
				total_interval += value;
			});

			// Store the frame number
			image.data("fnum", 0);
			var fnum = image.data("fnum");
			var timer;
			// Perform the animation by looping over the given total interval.
			// If the total interval already exists, reuse that timer.
			var animation = atTotalInterval(function() {
				clearTimeout(timer);
				fnum = image.data("fnum");
				timer = setTimeout(function() {
					var frame = o.base_path + frameList[fnum] + o.format;
					image.attr({src: frame}).data("fnum", fnum+1 >= frameList.length ? 0 : fnum+1);
				}, intervalList[fnum]);
			}, total_interval);

		});
	};

	$.fn.animateImg.defaults = {
		base_path: '/zgplace/public/img/',
		format: '.png',
		frames: null,
		image: null,
		intervals: 500
	};



	var _intervals = {};
	window._intervals = _intervals;

	// Stores all intervals in a single array so we only need one for each different sub interval.
	function atTotalInterval(callback, interval){
		if(_intervals[interval] == undefined){
			_intervals[interval] = {
				theinterval: setInterval(function(){
					for(var i=0, l=_intervals[interval].callbacks.length; i<l; ++i){
						_intervals[interval].callbacks[i].call();
					}
				}, interval),
				callbacks: [callback]
			};
		} else {
			_intervals[interval].callbacks.push(callback);
		}
	};

	/*var _timers = {};
	window._timers = _timers;

	// Stores all timers in a single array so we only need one for each different total interval.
	function atTimeout(callback, timer){
		if(_timers[timer] == undefined){
			_timers[timer] = {
				thetimer: setTimeout(function(){
					for(var i=0, l=_timers[timer].callbacks.length; i<l; ++i){
						_timers[timer].callbacks[i].call();
					}
				}, timer),
				callbacks: [callback]
			};
		} else {
			_timers[timer].callbacks.push(callback);
		}
	};*/

	/*$(document).bind("beforeunload", function(){
		$.each(_intervals, function(i, timer){
			clearTimeout(timer.timer);
		});
	});*/
})(jQuery);

