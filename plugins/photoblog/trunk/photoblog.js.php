var coords;

jQuery(document).ready(function(){
	jQuery('#pb_container').hide();

	coords = eval('('+decodeURIComponent(jQuery('#pb_coords').val())+')');
	
	jQuery('#pb_loadURL').click(function(){
		if ( jQuery('#photourl').val() == "" ) {
			return false;
		}
		$('#cropbox_container').empty().append('<img id="cropbox">');
		$('#preview_container').empty().append('<img id="preview">');
		
		originalImage = new Image();
		originalImage.src = jQuery('#photourl').val();

		jQuery('#cropbox,#preview').attr('src', originalImage.src);
		
		windowSize = get_window_size();
		var w = (windowSize.width - 100), h = (windowSize.height - 100);
		var nw = originalImage.width, nh = originalImage.height;
		if ((nw > w) && w > 0)
		{
			nw = w;
			nh = (w/originalImage.width) * originalImage.height;
		}
		if ((nh > h) && h > 0)
		{
			nh = h;
			nw = (h/originalImage.height) * originalImage.width;
		}
		xscale = originalImage.width / nw;
		yscale = originalImage.height / nh;
		jQuery('#pb_container').width(nw).height(nh);
		
		selectc = {	"x" : ((coords.x/xscale > 0) ? (coords.x/xscale) : 0),
					"y" : ((coords.y/xscale > 0) ? (coords.y/yscale) : 0),
					"x2" : ((coords.x2/xscale > 0) ? (coords.x2/xscale) : 150),
					"y2" : ((coords.y2/xscale > 0) ? (coords.y2/yscale) : 150) }

		jQuery('#cropbox').Jcrop({
			onChange: showPreview,
			onSelect: showPreview,
			aspectRatio: 1,
			boxWidth: w,
			boxHeight: h,
			setSelect: [selectc.x,selectc.y,selectc.x2,selectc.y2]
		});

		/* Viva el Thickbox hacking */
		tb_show( '<?php _e('Thumbnail selection') ?>', '#TB_inline?height=' + nh + '&width=' + nw + '&inlineId=pb_container', false );
		/* Have to wait for Thickbox to instantiate so jQuery binds to the right DOM objects */
		jQuery('#pb_loadURL').blur();
		jQuery('#TB_closeAjaxWindow').replaceWith("<div id='TB_closeAjaxWindow'><a href='#' id='TB_closeWindowButton' title='Close'>Close</a> <input type='button' id='pb_setThumb' name='pb_setThumb' value='<?php _e('Set Position') ?>'></div>");
		jQuery("#TB_closeWindowButton").click(tb_remove);
		jQuery('#pb_setThumb').click(function(){
			$('#pb_coords').val(encodeURIComponent(serialize(coords)));
			humanMsg.displayMsg('<?php _e('Thumbnail position successfully saved!') ?>');
		});
	});
});

function showPreview(c)
{
	var rx = 150 / c.w;
	var ry = 150 / c.h;

	jQuery('#preview').css({
		width: Math.round(rx * originalImage.width) + 'px',
		height: Math.round(ry * originalImage.height) + 'px',
		marginLeft: '-' + Math.round(rx * c.x) + 'px',
		marginTop: '-' + Math.round(ry * c.y) + 'px'
	});
	
	coords = c;
}

function serialize(_obj)
{
   // Other browsers must do it the hard way
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

function get_window_size()
{
	var w = 0;
	var h = 0;

	//IE
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
	//w3c
	else
	{
		w = window.innerWidth;
		h = window.innerHeight;
	}
	return {width:w,height:h};
}
