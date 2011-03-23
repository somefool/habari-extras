<?php

class PhotoSlide extends Plugin
{
	public function action_plugin_activation( $plugin_file )
	{
		Post::add_new_type( 'photoset' );
	}

	public function action_plugin_deactivation( $plugin_file )
	{
		Post::deactivate_post_type( 'photoset' );
	}
	

	public function filter_post_type_display($type, $foruse) 
	{ 
		$names = array( 
			'photo' => array(
				'singular' => _t( 'Photoset', 'photoslide' ),
				'plural' => _t( 'Photosets', 'photoslide' ),
			)
		); 
		return isset($names[$type][$foruse]) ? $names[$type][$foruse] : $type; 
	}
	
	public function action_form_publish( $form, $post )
	{
		if( $form->content_type->value == Post::type( 'photoset' ) ) {
			
			$assetdivs = '';
			if($post->content != '') {
				$assetdivs =  $this->assetlist_to_divs($post->content, $post, '<div class="setphoto"><img src="{asset_thumbnail}" style="width: 50px;"><a href="#delete">delete</a><input class="setphoto_caption" type="text" value="{caption}"><input class="setphoto_path" type="hidden" value="{asset_path}"></div>');
			}

			$imagepreview = $form->insert('content', 'static', 'imagepreview', '');
			$imagepreview->caption = <<< CAPTION_SCRIPT
<div class="container transparent">
	<fieldset>
		<legend>Photos</legend>
		<div id="photoset">
{$assetdivs}
		</div>
	</fieldset>
</div>
<script type="text/javascript">
function add_photo_to_set(fileindex, fileobj) {
	$('#photoset').append('<div class="setphoto"><img src="' + fileobj.thumbnail_url + '" style="width: 50px;"><a href="#delete">delete</a><input class="setphoto_caption" type="text" value="' + fileobj.basename + '"><input class="setphoto_path" type="hidden" value="' + fileobj.path + '"></div>');
	photoset_recompute();
}
function photoset_recompute() {
	var photos = [], captions = [];
	$('.setphoto').each(function(){
		photos.push($('.setphoto_path', this).val());
		captions.push($('.setphoto_caption', this).val());
	});
	$('#content').val(photos.join("\\n") + "\\n\\n" + captions.join("\\n"));
	$('#photoset').sortable({
		axis: 'y',
		opacity: 0.6,
		containment: $('#photoset').closest('.container'),
		stop: photoset_recompute
	});
}
$(function(){
	$.extend(habari.media.output.image_jpeg, {
		insert_image: add_photo_to_set
	});
	$.extend(habari.media.output.flickr, {
		embed_photo: add_photo_to_set
	});
	$('.setphoto a[href=#delete]').live('click', function(){
		$(this).closest('.setphoto').fadeOut(function(){\$(this).remove();photoset_recompute();});
		return false;
	});
	photoset_recompute();
});
</script>
CAPTION_SCRIPT;
						
			// Re-create the Content field
			//$form->content->remove();
			$content = $form->append( 'hidden', 'content', 'null:null' );
			//$content = $form->append( 'text', 'content', 'null:null', _t( 'Content' ), 'admincontrol_text' );
			$content->value = $post->content;
			$content->raw = true;
			$content->id = 'content';
			
//			$postfields = $form->publish_controls->append( 'fieldset', 'enclosures', _t( 'Podcasting', 'podcast'  ) );
		}
	}
	
	public function filter_post_content_media($content, $post)
	{
		return $this->assetlist_to_divs($content, $post, '<div class="setphoto" id="sp_{post_slug}_{index}"><img src="{asset_url}" alt="{caption}"></div>');
	}
	
	public function filter_post_content_thumbnails($content, $post)
	{
		return $this->assetlist_to_divs($content, $post, '<div class="setphoto" id="spthumb_{post_slug}_{index}"><img src="{asset_thumbnail}" alt="{caption}"></div>');
	}
	
	public function assetlist_to_divs($content, $post, $subst)
	{
		$assetdivs = '';
		if($content != '') {
			$assetdivs = array();
			$content = preg_split("#(\r\n|\n\r|\n){2,}#", $content);
			list($assets, $captions) = $content;
			$assets = explode("\n", $assets);
			$captions = explode("\n", $captions);
			reset($captions);
			$index = 0;
			foreach($assets as $asset) {
				$asset = Media::get(trim($asset));
				$caption = trim(current($captions));
				$repl = array(
					'{post_id}' => $post->id,
					'{post_slug}' => $post->slug,
					'{asset_url}' => $asset->url,
					'{asset_path}' => $asset->path,
					'{asset_thumbnail}' => $asset->thumbnail_url,
					'{caption}' => $caption,
					'{index}' => $index,
				);
				$assetdivs[] = str_replace(array_keys($repl), $repl, $subst);
				next($captions);
				$index++;
			}
			$assetdivs = implode("\n", $assetdivs);
		}
		return $assetdivs;
	}

	public function filter_post_content_mediaurl($content, $post)
	{
		return Media::get($content)->url;
	}
	
	
}


?>