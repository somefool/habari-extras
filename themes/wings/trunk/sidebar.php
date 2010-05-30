<!-- sidebar -->
					<h3>Search</h3>
					<div id="search">
						<?php $theme->display ('searchform' ); ?>
					</div>
					<h3><?php if($request->display_home) { echo "Other Entries"; } else { echo "Recent Entries"; } ?></h3>
					<div id="recent-entries">
						<ul>
							<?php
								if($request->display_home) { $limit = "5,15"; } else { $limit = "0,10"; }
								$entries = DB::get_results("SELECT * FROM ".DB::Table('posts')." WHERE content_type = '1' AND status = '2' ORDER BY pubdate DESC LIMIT $limit");
								foreach ($entries as $entry) {
							?>
							<li><a href="<?php Site::out_url( 'habari' ); ?>/<?php echo $entry->slug; ?>" title="<?php echo $entry->title; ?>"><?php echo $entry->title; ?></a></li>
							<?php
								}
							?>
						</ul>
					</div>
<?php					$theme->area( 'sidebar' ); ?>
<!-- /sidebar -->
