jQuery(document).ready(function(){
	jQuery('#pb_container').hide();
	
	jQuery('#pb_saveThumb').click(function(){
		$('#pb_x').val($('#pb_nx').val());
		$('#pb_y').val($('#pb_ny').val());
		$('#pb_x2').val($('#pb_nx2').val());
		$('#pb_y2').val($('#pb_ny2').val());
		$('#pb_w').val($('#pb_nw').val());
		$('#pb_h').val($('#pb_nh').val());
		alert(<?php _t('Position successfully saved') ?>);
	});
	
	jQuery('#pb_loadURL').click(function(){
		if ( jQuery('#photourl').val() == "" ) {
			return false;
		}
		$('#cropbox_container').empty().append('<img id="cropbox">');
		$('#preview_container').empty().append('<img id="preview">');
		
		originalImage = new Image();
		originalImage.src = jQuery('#photourl').val();

		jQuery('#cropbox,#preview').attr('src', originalImage.src);

		var w = 800, h = 600;
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

		jQuery('#cropbox').Jcrop({
			onChange: showPreview,
			onSelect: showPreview,
			aspectRatio: 1,
			boxWidth: w,
			boxHeight: h,
			setSelect: [0,0,150,150]
		});

		jQuery('#pb_container').slideDown("slow");
	});
});

function showPreview(coords)
{
	var rx = 150 / coords.w;
	var ry = 150 / coords.h;

	jQuery('#preview').css({
		width: Math.round(rx * originalImage.width) + 'px',
		height: Math.round(ry * originalImage.height) + 'px',
		marginLeft: '-' + Math.round(rx * coords.x) + 'px',
		marginTop: '-' + Math.round(ry * coords.y) + 'px'
	});
	
	$('#pb_nx').val(coords.x);
	$('#pb_ny').val(coords.y);
	$('#pb_nx2').val(coords.x2);
	$('#pb_ny2').val(coords.y2);
	$('#pb_nw').val(coords.w);
	$('#pb_nh').val(coords.h);
}