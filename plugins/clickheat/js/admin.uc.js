/**
 * Clickehat by Benjamin Hutchins
 * @see Plugin info for more information
 */
var clickheat = {

	/**
	 * Quickly get the timestamp
	 */
	time: Date.now || function(){ return +new Date; },

	alpha: 70,
	lastDayOfMonth: 0,
	date: [],
	range: 'd',
	width: 0,
	caldate: '',
	path: habari.url.clickheat,

	init: function() {
		// add events
		$('#group select').change(function(){ clickheat.loadIframe(); });
		$('#screen select').change(function(){ clickheat.resizeDiv(); clickheat.updateHeatmap(); });
		$('#heatmap input').click(function(){ clickheat.updateHeatmap(); });

		// start clickheat
		this.drawAlphaSelector(60);
		this.resizeDiv();
		this.loadIframe();
		this.runCleaner();
	},

	/**
	 * Hide/Show options
	 */
	toggleVisibility: function(e)
	{
		e=$(e);
		$('#options').toggle();
		var backup= e.html();
		e.html( e.attr('alt') );
		e.attr('alt', backup);
	},

	/**
	 * Returns the "top" value of an element
	 */
	getTop: function(obj)
	{
		if (typeof obj == 'string') obj= document.getElementById(obj);

		if (obj.offsetParent != undefined)
			return (obj.offsetTop + this.getTop(obj.offsetParent));
		else
			return obj.offsetTop;
	},

	/**
	 * Resize the div relative to window height and selected screen size
	 */
	resizeDiv: function()
	{
		oD = document.documentElement != undefined && document.documentElement.clientHeight != 0 ? document.documentElement : document.body;
		iH = oD.innerHeight != undefined ? oD.innerHeight : oD.clientHeight;
		$('overflowDiv').css('height', (iH < 300 ? 400 : iH) - clickheat.getTop('overflowDiv') + 'px');

		/**
		 * Width of main display
		 */
		iW = oD.innerWidth != undefined ? oD.innerWidth : oD.clientWidth;

		clickheat.width = $('#screen select').val() == 0 ? iW : $('#screen select').val() - 5;

		$('#overflowDiv').css('width', clickheat.width + 'px');
		$('#webPageFrame').css('width', (clickheat.width - 25) + 'px');
	},

	/**
	 * Update calendar selected days
	 */
	updateCalendar: function(day)
	{
		/** clickheat.date[day, month, year, saved_day, month_origin, year_origin] */
		if (day != undefined)
			clickheat.date[3] = day;

		clickheat.date[1] = clickheat.date[4];
		clickheat.date[2] = clickheat.date[5];
		/** Showing one day */
		if (clickheat.range == 'd')
		{
			/** Remember the last day used */
			clickheat.date[0] = clickheat.date[3];
			min = clickheat.date[0];
			max = clickheat.date[0];
		}
		/** Showing one month */
		if (clickheat.range == 'm')
		{
			clickheat.date[0] = 1;
			min = 1;
			max = weekDays.length;
		}
		/** Showing one week */
		if (clickheat.range == 'w')
		{
			/** Remember the last day used */
			clickheat.date[0] = clickheat.date[3];
			week = weekDays[clickheat.date[0]];
			min = 0;
			max = 0;
			for (d = 1; d < weekDays.length; d++)
				if (weekDays[d] == week)
				{
					if (min == 0)
					{
						clickheat.date[0] = d;
						min = d;
					}
					max = d;
				}

			/** Start was on the previous month */
			if (min == 1 && max != 7)
			{
				clickheat.date[0] = clickheat.lastDayOfMonth - 6 + max;
				clickheat.date[1]--;
				if (clickheat.date[1] == 0)
				{
					clickheat.date[1] = 12;
					clickheat.date[2]--;
				}
			}
		}
		for (d = 1; d < weekDays.length; d++)
			document.getElementById('clickheat-calendar-' + d).className = (d >= min && d <= max ? 'clickheat-calendar-on' : '');

		for (i = 1; i < 7; i++)
		{
			if (document.getElementById('clickheat-calendar-10' + i) != undefined)
				document.getElementById('clickheat-calendar-10' + i).className = (clickheat.range == 'w' && weekDays[min] == weekDays[1] ? 'clickheat-calendar-on' : '');

			if (document.getElementById('clickheat-calendar-11' + i) != undefined)
				document.getElementById('clickheat-calendar-11' + i).className = (clickheat.range == 'w' && weekDays[max] == weekDays[weekDays.length - 1] ? 'clickheat-calendar-on' : '');

		}
		document.getElementById('clickheat-calendar-d').className = (clickheat.range == 'd' ? 'clickheat-calendar-on' : '');
		document.getElementById('clickheat-calendar-w').className = (clickheat.range == 'w' ? 'clickheat-calendar-on' : '');
		document.getElementById('clickheat-calendar-m').className = (clickheat.range == 'm' ? 'clickheat-calendar-on' : '');

		this.updateHeatmap();
	},

	/**
	 * Ajax request to update PNGs
	 */
	updateHeatmap: function()
	{
		$('#pngDiv').html('&nbsp;<div style="line-height:20px"><span class="error"><?php _e("Loading"); ?></span></div>');

		$.ajax({
			url: clickheat.path,
			data: {
				action: 'generate',
				group: $('#group select').val(),
				screen: $('#screen select').val() == 0 ? -1 * clickheat.width + 25 : $('#screen select').val(),
				date: clickheat.date[2] + '-' + clickheat.date[1] + '-' + clickheat.date[0],
				range: clickheat.range,
				heatmap: ($('#heatmap input').is(':checked') ? 1 : 0),
				rand: this.time()
			},
			success: function(r) {
				$('#pngDiv').html(r);
				$('#webPageFrame').css('height', $('#pngDiv').css('height'));
				clickheat.changeAlpha(clickheat.alpha);
			}
		});
	},

	/**
	 * Ajax request to get associated group in iframe
	 */
	loadIframe: function()
	{
		$.ajax({
			url: clickheat.path,
			data: {
				action: 'iframe',
				group: $('#group select').val(),
				rand: this.time()
			},
			success: function(r) {
				if ($('#webPageFrame').attr('src') != 'about:blank') {
					$('#webPageFrame').attr('src', r);
					clickheat.updateCalendar();
				} else {
					$('#webPageFrame').attr('src', r);
					clickheat.updateHeatmap();
				}
			}
		});
	},

	/**
	 * Draw alpha selector
	 */
	drawAlphaSelector: function(max)
	{
		var str = '';
		for (i = 0; i < max; i++) {
			grey = 255 - Math.ceil(i * 255 / max);
			alpha = Math.ceil(i * 100 / max);
			str += '<a href="#" id="alpha-level-' + alpha + '" onclick="clickheat.changeAlpha(' + alpha + '); this.blur(); return false;" style="font-size:12px; border-top:1px solid #888; border-bottom:1px solid #888;' + (i == 0 ? ' border-left:1px solid #888;' : '') + '' + (i == max - 1 ? ' border-right:1px solid #888;' : '') + ' text-decoration:none; background-color:rgb(' + grey + ',' + grey + ',' + grey + ');">&nbsp;</a>';
		}
		document.getElementById('alphaSelector').innerHTML = str;

		// Check that the current alpha exists
		while (document.getElementById('alpha-level-' + clickheat.alpha) == undefined)
		{
			clickheat.alpha--;
		}
	},

	/**
	 * Change Alpha on heatmap
	 */
	changeAlpha: function(alpha)
	{
		document.getElementById('alpha-level-' + clickheat.alpha).style.borderTop = '1px solid #888';
		document.getElementById('alpha-level-' + clickheat.alpha).style.borderBottom = '1px solid #888';
		clickheat.alpha = alpha;
		document.getElementById('alpha-level-' + clickheat.alpha).style.borderTop = '2px solid #55b';
		document.getElementById('alpha-level-' + clickheat.alpha).style.borderBottom = '2px solid #55b';
		for (i = 0; i < document.images.length; i++)
		{
			if (document.images[i].id.search(/^heatmap-\d+$/) == 0)
			{
				document.images[i].style.opacity = alpha / 100;
				if (document.body.filters != undefined)
				{
					document.images[i].style.filter = 'alpha(opacity:' + alpha + ')';
				}
			}
		}
	},

	/**
	 * Ajax request to show javascript code
	 */
	runCleaner: function()
	{
		$('#cleaner').html('<?php _e("Cleaner is running"); ?>');
		$.ajax({
			url: clickheat.path,
			data: {
				action: 'cleaner',
				rand: this.time()
			},
			success: function(r) {
				var msg= 'Clickheat <?php _e("made by"); ?> <a href="http://www.xvolter.com/">Benjamin Hutchins</a>';
				if (r == 'OK')
					$('#cleaner').html(msg);

				else {
					$('#cleaner').html(r);
					setTimeout("$('#cleaner').html('"+msg+"');", 10000);
				}
			}
		});
	}
};
