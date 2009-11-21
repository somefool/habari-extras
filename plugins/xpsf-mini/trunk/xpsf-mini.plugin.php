<?php
/*
* XSPF Mini Plugin for Habari
* Embeds an audio player in posts
 * @version $Id$
 * @copyright 2008
 */

Class XSPFMini extends Plugin
{

	public function action_update_check()
	{
		Update::add( 'XSPF Mini', '0DE511F0-7890-11DD-9F6E-3D7356D89593',  $this->info->version );
	}

	public function filter_post_content_out( $content )
	{
		preg_match_all( '/<!-- file:(.*) -->/i', $content, $matches, PREG_PATTERN_ORDER );
		$matches_obj = new ArrayObject( $matches[1] );

		for( $it = $matches_obj->getIterator(); $it->valid(); $it->next() ){
			$content = str_ireplace( '<!-- file:' . $it->current() . ' -->', $this->embed_player( $it->current() ), $content );
		}

		return $content;
	}

	public function embed_player( $file )
	{
		$player = '<p><object width="300" height="20">';
		$player .= '<param name="movie" value="' . $this->get_url() . '/xspf_player_slim.swf?song_url=' . $file . '&song_title=' . basename( $file, '.mp3' ) . '&player_title=' . htmlspecialchars( Options::get( 'title' ), ENT_COMPAT, 'UTF-8' ) . '" />';
		$player .= '<param name="wmode" value="transparent" />';
		$player .= '<embed src="' . $this->get_url() . '/xspf_player_slim.swf?song_url=' . $file . '&song_title=' . basename( $file, '.mp3' ). '&player_title=' . htmlspecialchars( Options::get( 'title' ), ENT_COMPAT, 'UTF-8' ) . '" type="application/x-shockwave-flash" wmode="transparent" width="300" height="20"></embed>';
		$player .= '</object></p>';

		return $player;
	}

	public function action_admin_header_after( $theme ) 
	{
		echo <<< XSPFMINI

<script type="text/javascript">
$.extend(habari.media.output.audio_mpeg3, {
add_xspfmini_player: function(fileindex, fileobj) {
habari.editor.insertSelection('<!-- file:'+fileobj.url+' -->');
}});
</script>

XSPFMINI;
	}
}
?>