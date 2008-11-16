/**
 * Clickehat by Benjamin Hutchins
 * @see Plugin info for more information
 */

/**
 * Clickheat command object.
 * Uses no javascript framework so you can use
 * any you want up front.
 */
var clickheat = {

	/**
	 * Variables
	 */
	server: "<?php URL::out('ajax', 'context=clickheat&action=click'); ?>",
	wait: <?php Options::out('clickheat__wait'); ?>,
	quota: <?php Options::out('clickheat__quota'); ?>,
	doc: null,
	useDebug: true,//(window.location.href.search(/debugclickheat/) !== -1),
	lastTime: -1,
	ajax: false,

	/**
	 * Wrapper to get timestamp
	 */
	time: Date.now || function(){ return +new Date; },

	/**
	 * Initialize the JavaScript
	 */
	init: function()
	{
		/**
		 * If current website has the same domain as the script,
		 * we remove the domain so that the call is made using Ajax
		 */
		domain = window.location.href.match(/http:\/\/[^/]+\//);
		if (domain !== null && this.server.substring(0, domain[0].length) === domain[0]) {
			this.server = this.server.substring(domain[0].length - 1, this.server.length);
			this.ajax= true;
		}

		if (document.addEventListener)
			document.addEventListener('mousedown', clickheat.click, false);
		else
			document.attachEvent('onmousedown', clickheat.click);

		/** Preparing main variables */
		this.doc = (document.documentElement !== undefined && document.documentElement.clientHeight !== 0) ? document.documentElement : document.body;

		this.debug('ClickHeat initialized.');
	},

	/**
	 * Output debug message
	 */
	debug: function(d)
	{
		if ( clickheat.useDebug && window.console )
			console.log(d);
		return true;
	},

	/**
	 */
	xhr: function( url, callback, data, method )
	{
		if (method === undefined) method= 'get';
		var xhr= window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
		if (data && method == 'get'){
			url = url + (url.indexOf('?') != -1 ? '&' : '?') + data;
			data = null;
		}
		xhr.open(method.toUpperCase(), url, true);
		xhr.onreadystatechange = function(){
			if ( xhr.readyState != 4 ) return;
			if ((xhr.status >= 200) && (xhr.status < 300)) {
				callback(xhr.responseText, xhr.responseXML);
			} else {
				clickheat.debug('Server returned a status code ' + xhr.status + ' with the following error: ' + xhr.responseText);
			}
			xhr.onreadystatechange = function(){};
		};
		xhr.setRequestHeader('Connection', 'close');
		xhr.send(null);
	},

	/**
	 * Quick and dirty object to params
	 */
	toQueryString: function(obj)
	{
		var queryString = [];
		for (var key in obj)
			queryString.push( key + '=' + encodeURIComponent(obj[key]) );
		return queryString.join('&');
	},

	/**
	 * Core of clientheat
	 */
	click: function(event)
	{
		if (this.quota === 0)
			return clickheat.debug('Click not logged: quota reached');

		var e = event || window.event;
		var client = { x: (e.pageX) ? e.pageX - window.pageXOffset : e.clientX, y: (e.pageY) ? e.pageY - window.pageYOffset : e.clientY };

		clickheat.debug('New click. Gathering data...');

		var x = client.x;
		var y = client.y;
		var w = clickheat.doc.clientWidth !== undefined ? clickheat.doc.clientWidth : window.innerWidth;
		var h = clickheat.doc.clientHeight !== undefined ? clickheat.doc.clientHeight : window.innerHeight;
		var scrollx = window.pageXOffset === undefined ? clickheat.doc.scrollLeft : window.pageXOffset;
		var scrolly = window.pageYOffset === undefined ? clickheat.doc.scrollTop : window.pageYOffset;
		var which = e.button || e.which;

		/**
		 * Is the click in the viewing area? Not on scrollbars?
		 * The problem still exists for FF on the horizontal scrollbar
		 */
		if (x > w || y > h)
			return clickheat.debug('Click not logged: out of document (should be a click on scrollbars)');

		/**
		 * Check if last click was at least 1 second ago
		 */
		clickTime = clickheat.time();
		if (clickTime - clickheat.lastTime < 1000)
			return clickheat.debug('Click not logged: at least 1 second between clicks');

		clickheat.lastTime = clickTime;
		clickheat.quota--;

		var params = clickheat.toQueryString({
			title: document.title,
			href: window.location.href,
			x: (x + scrollx),
			y: (y + scrolly),
			w: w,
			which: which,
			random: clickTime // forced reload when using Image
		});
		clickheat.debug('Ready to send click data...');

		/**
		 * Local request? Try an ajax call
		 */
		if ( clickheat.ajax ) {
			clickheat.xhr(clickheat.server, function(response){
				clickheat.debug('Click recorded with the following parameters:\n' + params + '\n\nServer answer: ' + response);
			}, params);
		} else	// send request via an image
			new Image().src = clickheat.server + '?' + params;

		/**
		 * Little waiting cycle
		 * We need to wait to make sure the request is sent properly
		 */
		var waiting= clickheat.time() + clickheat.wait;
		var func=function(){};
		while ( waiting > clickheat.time() )
			// wish javascript had sleep
			func();

		return true;
	}
};
