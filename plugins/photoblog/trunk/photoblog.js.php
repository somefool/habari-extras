var thumb;
var defaults = { // Default size of thumbnail, could be different on a per-post basis
	'w': <?php echo $options['thumbnail_w'] ?>,
	'h': <?php echo $options['thumbnail_h'] ?>
};
var windowOffset = { // Lets a gap between the Thickbox and browser borders so users can click out
	'width': 100,
	'height': 100
};
var tb_pathToImage = '<?php echo $this->get_url() ?>/images/loadingAnimation.gif';

$(document).ready(function() {
	$('#pb_container').hide();

    $('#pb_loadURL').click(function() {
		if ($('#pb_photo_src').val() == "") {
		    return false;
		}
		
		thumb = eval('(' + decodeURIComponent($('#pb_coords').val()) + ')');

		if (thumb.w <= 0) { thumb.w = defaults.w; }
		if (thumb.h <= 0) { thumb.h = defaults.h; }

		/* Cleaning old thumbnail, otherwise jCrop won't be clever */
		$('#pb_cropbox_container').empty().append('<img id="pb_cropbox">');
		$('#pb_preview_container').empty().append('<img id="pb_preview">');

		originalImage = new Image();
		originalImage.src = $('#pb_photo_src').val();
		$(originalImage).load(function() {
			$('#pb_cropbox,#pb_preview').attr('src', originalImage.src);

			windowSize = get_window_size();
			var w = (windowSize.width - windowOffset.width), h = (windowSize.height - windowOffset.height);
			var nw = originalImage.width, nh = originalImage.height;
			if ((nw > w) && w > 0)
			{
				nw = w;
				nh = (w / originalImage.width) * originalImage.height;
			}
			if ((nh > h) && h > 0)
			{
				nh = h;
				nw = (h / originalImage.height) * originalImage.width;
			}
			xscale = originalImage.width / nw;
			yscale = originalImage.height / nh;
			$('#pb_container').width(nw).height(nh);
			$('#pb_preview_container').width(defaults.w).height(defaults.h);

			selectc = {
				"x": ((thumb.x / xscale > 0) ? (thumb.x / xscale) : 0),
				"y": ((thumb.y / xscale > 0) ? (thumb.y / yscale) : 0),
				"x2": ((thumb.x2 / xscale > 0) ? (thumb.x2 / xscale) : thumb.w),
				"y2": ((thumb.y2 / xscale > 0) ? (thumb.y2 / yscale) : thumb.h)
			}

			$('#pb_cropbox').Jcrop({
				onChange: showPreview,
				onSelect: showPreview,
				aspectRatio: 1,
				boxWidth: w,
				boxHeight: h,
				setSelect: [selectc.x, selectc.y, selectc.x2, selectc.y2]
	        });

			/* Viva el Thickbox hacking */
			tb_show('<?php _e('Thumbnail selection ') ?>', '#TB_inline?height=' + nh + '&width=' + nw + '&inlineId=pb_container', false);
			$('#pb_loadURL').blur();
			$('#TB_closeAjaxWindow').replaceWith("<div id='TB_closeAjaxWindow'><a href='#' id='TB_closeWindowButton' title='<?php _e('Close') ?>'><?php _e('Close') ?></a> <input type='button' id='pb_setThumb' name='pb_setThumb' value='<?php _e('Set Position') ?>'></div>");

			/* Have to wait for Thickbox to instantiate so jQuery binds to the right DOM objects */
			$("#TB_closeWindowButton").click(tb_remove);
			$('#pb_setThumb').click(function() {
				$('#pb_coords').val(encodeURIComponent(serialize(thumb)));
				humanMsg.displayMsg('<?php _e('Thumbnail position successfully saved !') ?>');
			});
		});
	});
	
	<?php if (Plugins::is_loaded('Habari Media Silo')): ?>
	$.extend(habari.media.output.image_jpeg, {
		use_as_large_photo: function(fileindex, fileobj) {set_photo(fileindex, fileobj);}
	});
	$.extend(habari.media.output.image_png, {
		use_as_large_photo: function(fileindex, fileobj) {set_photo(fileindex, fileobj);}
	});
	$.extend(habari.media.output.image_gif, {
		use_as_large_photo: function(fileindex, fileobj) {set_photo(fileindex, fileobj);}
	});
	function set_photo(fileindex, fileobj) {
		$('#pb_photo_src').val(fileobj.originalsecret).focus();
	}
	<?php endif; ?>
	<?php if (Plugins::is_loaded('Flickr Media Silo')): ?>
	$.extend(habari.media.output.flickr, {
		use_small_photo: function(fileindex, fileobj) {set_flickr_photo(fileindex, fileobj, '_m.');},
		use_medium_photo: function(fileindex, fileobj) {set_flickr_photo(fileindex, fileobj, '_-.');},
		use_large_photo: function(fileindex, fileobj) {set_flickr_photo(fileindex, fileobj, '_b.');},
		use_original_photo: function(fileindex, fileobj) {set_flickr_photo(fileindex, fileobj, '_o.');}
	});
	function set_flickr_photo(fileindex, fileobj, filesize) {
		if (filesize == '_o.') {
			if (fileobj.originalsecret && fileobj.originalformat) {
				fileobj_secret = fileobj.originalsecret;
				fileobj_size = '_o.';
				fileobj_format = fileobj.originalformat;
			}
			else {
				humanMsg.displayMsg('<?php _e('Original photo unavailable, pro account required.') ?>');
			}
		}
		else {
			fileobj_secret = fileobj.secret;
			fileobj_size = filesize;
			fileobj_format = 'jpg';
		}
		$('#pb_photo_src').val('http://farm' + fileobj.farm + '.static.flickr.com/' + fileobj.server + '/' + fileobj.id + '_' + fileobj_secret + fileobj_size + fileobj_format).focus();
	}
	<?php endif; ?>
});

/* 
 * Moves the photo around the preview box
 * We also save the marquee's coords
 */
function showPreview(c)
 {
	var rx = thumb.w / c.w;
	var ry = thumb.h / c.h;

	$('#pb_preview').css({
		width: Math.round(rx * originalImage.width) + 'px',
		height: Math.round(ry * originalImage.height) + 'px',
		marginLeft: '-' + Math.round(rx * c.x) + 'px',
		marginTop: '-' + Math.round(ry * c.y) + 'px'
	});

	/* Tracking marquee position */
	thumb.x = c.x;
	thumb.y = c.y;
	thumb.x2 = c.x2;
	thumb.y2 = c.y2;
	thumb.w2 = c.w;
	thumb.h2 = c.h;
}

/*
 * Serializes a JSON object
 * Credit: Can't find the place I found this, if anyone knows, please add link
 */
function serialize(_obj)
{
	switch (typeof _obj)
	{
		// numbers, booleans, and functions are trivial:
		// just return the object itself since its default .toString()
		// gives us exactly what we want
		case 'number':
		case 'boolean':
		case 'function':
			return _obj;
			break;

		// for JSON format, strings need to be wrapped in quotes
		case 'string':
			return '"' + _obj + '"';
			break;

		case 'object':
			var str;
			if (_obj.constructor === Array || typeof _obj.callee !== 'undefined')
			{
				str = '[';
				var i, len = _obj.length;
				for (i = 0; i < len-1; i++) { str += serialize(_obj[i]) + ','; }
				str += serialize(_obj[i]) + ']';
			}
			else
			{
				str = '{';
				var key;
				for (key in _obj) { str += '"' + key + '":' + serialize(_obj[key]) + ','; }
				str = str.replace(/\,$/, '') + '}';
			}
			return str;
			break;

		default:
			return 'UNKNOWN';
			break;
	}
}

/*
 * Retrieve document size so we can resize the Thickbox to fit
 */
function get_window_size()
{
	var w = 0;
	var h = 0;

	// IE
	if(!window.innerWidth)
	{
		//strict mode
		if(!(document.documentElement.clientWidth == 0))
		{
			w = document.documentElement.clientWidth;
			h = document.documentElement.clientHeight;
		}
		//quirks mode
		else
		{
			w = document.body.clientWidth;
			h = document.body.clientHeight;
		}
	}
	// W3C
	else
	{
		w = window.innerWidth;
		h = window.innerHeight;
	}
	return {width:w,height:h};
}