var thumb;
var defaults = { // Default size of thumbnail, could be different on a per-post basis
	'w': <?php echo $options['thumbnail_w'] ?>,
	'h': <?php echo $options['thumbnail_h'] ?>
};
var windowOffset = { // Lets a gap between the Thickbox and browser borders so users can click out
	'width': 100,
	'height': 100
};

$(document).ready(function() {
	$('#pb_container').hide();

	thumb = eval('(' + decodeURIComponent($('#pb_coords').val()) + ')');

    $('#pb_loadURL').click(function() {
		if ($('#photourl').val() == "") {
		    return false;
		}

		/* Cleaning old thumbnail, otherwise jCrop won't be clever */
		$('#cropbox_container').empty().append('<img id="cropbox">');
		$('#preview_container').empty().append('<img id="preview">');

		originalImage = new Image();
		originalImage.src = $('#photourl').val();

		$('#cropbox,#preview').attr('src', originalImage.src);

		windowSize = get_window_size();
		var w = (windowSize.width - windowOffset.width),
		h = (windowSize.height - windowOffset.height);
		var nw = originalImage.width,
		nh = originalImage.height;
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
		$('#preview_container').width(defaults.w).height(defaults.h);

		selectc = {
			"x": ((thumb.x / xscale > 0) ? (thumb.x / xscale) : 0),
			"y": ((thumb.y / xscale > 0) ? (thumb.y / yscale) : 0),
			"x2": ((thumb.x2 / xscale > 0) ? (thumb.x2 / xscale) : thumb.w),
			"y2": ((thumb.y2 / xscale > 0) ? (thumb.y2 / yscale) : thumb.h)
		}

		$('#cropbox').Jcrop({
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
			/* We need the scales to convert coords to real size */
			thumb.xscale = xscale;
			thumb.yscale = yscale;

			/* In case the thumbnail size changes */
			thumb.w = defaults.w;
			thumb.h = defaults.h;

			$('#pb_coords').val(encodeURIComponent(serialize(thumb)));
			humanMsg.displayMsg('<?php _e('Thumbnail position successfully saved !') ?>');
		});
	});
});

/* 
 * Moves the photo around the preview box
 * We also save the marquee's coords
 */
function showPreview(c)
 {
	var rx = thumb.w / c.w;
	var ry = thumb.h / c.h;

	$('#preview').css({
		width: Math.round(rx * originalImage.width) + 'px',
		height: Math.round(ry * originalImage.height) + 'px',
		marginLeft: '-' + Math.round(rx * c.x) + 'px',
		marginTop: '-' + Math.round(ry * c.y) + 'px'
	});

	/* Tracking marquee position */
	thumb.x = c.x;
	thumb.y = c.y;
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