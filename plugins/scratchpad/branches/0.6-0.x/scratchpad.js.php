(function() {
function bookmarklet() {
	if (byId("hsp__div")) {
		return;
	}
	var w=window,d=document,gS='getSelection';
	var selection=((''+(w[gS]?w[gS]():d[gS]?d[gS]():d.selection.createRange().text)).replace(/(^\s+|\s+$)/g,''));
	var title=d.title;
	var hsp_div=div();
	hsp_div.id = "hsp__div";
	hsp_div.style.position = "absolute";
	hsp_div.style.top = scrollPos().y + "px";
	hsp_div.style.right = "0";
	hsp_div.style.zIndex = 100000;
	var fg = div(hsp_div);
	fg.id = "hsp__fg";
	fg.style.backgroundColor = "#F0F0F0";
	fg.style.zIndex = 2;
	fg.style.width = "450px";
	fg.style.height = "190px";
	fg.innerHTML = '<iframe frameborder="0" id="hsp__iframe" style="width:100%;height:100%;border:solid;1px;padding:0px;margin:0px"><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><title>-</title></head></html></iframe>';
	d.body.appendChild(hsp_div);
	var msg = {title:title, url:location.href};
	msg.selection = selection || '';
	sendFrameMessage(msg);
	window.onscroll = function() {
		hsp_div.style.top = scrollPos().y + "px";
	};
}
function scrollPos() {
	if (self.pageYOffset !== undefined) {
		return {
			x: self.pageXOffset,
			y: self.pageYOffset
		};
	}
	var d = document.documentElement;
	return {
		x: d.scrollLeft,
		y: d.scrollTop
	};
}
function div(opt_parent) {
	var e = document.createElement("div");
	e.style.padding = "0";
	e.style.margin = "0";
	e.style.border = "0";
	e.style.position = "relative";
	if (opt_parent) {
		opt_parent.appendChild(e);
	}
	return e;
}
function byId(id) {
	return document.getElementById(id);
}
function sendFrameMessage(m) {
	var p = "";
	for (var i in m) {
		if (!m.hasOwnProperty(i))
			continue;
		p += (p.length ? '&' : '');
		p += encodeURIComponent(i) + '=' + encodeURIComponent(m[i]);
	}
	var iframe;
	if (navigator.userAgent.indexOf("Safari") != -1) {
		iframe = frames["hsp__iframe"];
	} else {
		iframe = byId("hsp__iframe").contentWindow;
	}
	if (!iframe) return;
	var url = '<?php echo Site::get_url('habari'); ?>/scratchpad?' + p;
	try {
		iframe.location.replace(url);
	} catch (e) {
		iframe.location = url; // safari
	}
}
bookmarklet();
})();
