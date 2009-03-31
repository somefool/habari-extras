<?php $theme->display('header'); ?>
<!-- plugin.single.php -->
<?php 
$version = array();
$allversions = "";

$version_fields = array(
		'id', /* this is not in the directory, but needs to be here */
		'post_id',
		'version',
		'md5',
		'habari_version',
		'description',
		'author',
		'author_url',
		'license',
		'screenshot',
		'instructions',
		'url',
		'status',
		'requires',
		'provides',
		'recommends',
		'source_link'
	);



	foreach( (array) $post->versions as $this_version) 
	{ 
		$allversions .= ( $allversions ? " and " . $this_version->habari_version : $this_version->habari_version);

		foreach ($version_fields as $field) { 
			$version[ $this_version->habari_version ][$field] = $this_version->{"$field"};
		}
	}
krsort( $version); // ensure newest release first, cheap hack for now, though.
?>

<div class="push-5 span-19">
<h2 class="plugin_title"><a href="<?php echo $post->permalink; ?>"><?php 
echo $post->title_out; ?></a></h2><p class="plugin_title"><?php _e( " for Habari "); ?><?php echo 
$allversions; ?></p>
</div>
</div>
<div class="container">

<div class="column span-4">
<?php 	$theme->display('recent'); ?>
</div><!-- /column span-4 -->

<?php if ( ! empty ( $version ) ) : ?>

<div id="version_wrapper" class="column push-1 span-16">
<div id="version_accordion" class="ui-accordion-container">
	<?php foreach( $version as $key => $version ) : ?>
<div class="olderpostbox">
<div id="x-<?php echo $post->id; ?>-<? echo $version['version']; ?>" class="versiontitle"><?php echo 
$post->title_out; ?> <?php echo $version[ 'habari_version' ]; ?>-<?php echo $version[ 
'version' ]; 
?></div>

<div class="versioncontent container span-16">
	<div class="version_info column span-4">
		<h3>Download</h3>
<?php			$this_title = "$post->title_out $key-{$version[ 'version' ]}" ; ?>
			<a href="<?php echo $version[ 'url' ]; ?>" title="download <?php echo $this_title; ?>"><?php 
			echo $this_title; ?></a>

		<h3>Info</h3>
		<ul>
		<li>Status: <?php echo $version['status']; ?></li>
		<li>Author: <a href="<?php echo $version['author_url']; ?>" rel="nofollow"><?php 
echo $version['author']; ?></a></li>
<?php $license_post = Posts::get(array('where'=>'{posts}.id in (select post_id from {postinfo} where value="' . 
$version['license'] . '" and name="shortname")') ); ?>

		<li>License: <a href="<?php echo $license_post[0]->permalink; ?>" title="More details about this license"><?php echo $license_post[0]->title; ?></a></li>
<?php		if ($version['url']) : ?>
		<li>Plugin Link: <a href="<?php echo $version['url']; ?>" title="More information about this plugin"><?php _e("click here"); ?></a></li>
<?php		endif; ?>
		</ul>

	</div><!-- /version_info -->

	<div class="plugin_tabs_container span-12 last">
		<ul class="plugin_tabs">
			<li><a href="#description<?php echo $version[ 'id']; ?>">Description</a></li>
			<li><a href="#instructions<?php echo $version[ 'id']; ?>">Instructions</a></li><?php 
		if ($version[ 'screenshot' ]) : ?>
			<li><a href="#screenshot<?php echo $version[ 'id']; ?>">Screenshot</a></li>
		<?php endif; ?>
		</ul>

		<div id="description<?php echo $version[ 'id']; ?>">
		<h3>[plugin] Description</h3>
		<?php echo $post->content_out; ?>
		<h3>[version] Description</h3>
		<?php echo $version['description']; ?>
		</div><!-- /description -->

		<div id="instructions<?php echo $version[ 'id']; ?>">
		<h3>Instructions</h3>

		<?php echo Format::autop( $version['instructions'] ); ?>
		</div><!-- /instructions -->

		<?php if ($version[ 'screenshot' ]) : ?>
		<div id="screenshot<?php echo $version[ 'id']; ?>" class="screenshot">
		<h3>Screenshot</h3>
		<a href="<?php 
		echo $version[ 'screenshot' ]; ?>" title="<?php echo $post->title_out ?> Screenshot" class="thickbox"><img src="<?php 
		echo $version[ 'screenshot' ]; ?>" alt="screenshot of <?php echo $post->title_out; ?>"></a>
		</div><!-- /screenshot -->
		<?php endif; ?>

	</div><!-- /plugin_tabs_container -->

</div><!-- /versioncontent -->
</div><!-- /olderpostbox -->
<?php endforeach; ?>
</div><!-- /accordion_container -->
    </div><!-- /version_wrapper -->
<?php endif; ?>

<div class="column span-3 last">

<?php if ( is_array( $post->tags ) ) { ?>

	<div id="tags" class="box"><?php _e('Tagged:'); ?><ul>
	<?php foreach( $post->tags as $tag ) {  ?>
		<li><a href="<?php echo URL::get( 'display_entries_by_tag', array( 'tag' => $tag ) ); ?>"><?php echo $tag; ?></a></li>
	<?php } ?>
	</ul></div><!-- /tags -->
<?php } ?>

<div class="box"><?php _e('Most Popular Tags:'); ?>
<? $theme->tag_cloud(); ?>
</div>
</div>

<!-- /plugin.single.php -->
<?php $theme->display('footer'); ?>



