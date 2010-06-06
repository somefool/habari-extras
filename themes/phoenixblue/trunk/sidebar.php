</div>

<!-- sidebar -->
<?php Plugins::act( 'theme_sidebar_top' ); ?>
<div id="sidebar">
 <!-- searchform -->
  <h2><label for="s"><?php _e('Search:'); ?></label></h2>
  <ul>
    <li>
      <?php Plugins::act( 'theme_searchform_before' ); ?>
      <form id="searchform" method="get" action="<?php URL::out( 'display_search' ); ?>">
        <p><input type="text" name="criteria" id="s" size="15"></p>
        <p><input type="submit" name="submit" value="<?php _e('Search'); ?>"></p>
      </form>
      <?php Plugins::act( 'theme_searchform_after' ); ?>
   </li>
  </ul> 
  
  <!-- /searchform -->
  <?php

  if ( count( $pages ) != 0 ):
    ?>
    <h2><?php _e('Pages:'); ?></h2>
    <ul>
      <?php
        foreach ( $pages as $page ) {
          echo "<li><a href=\"{$page->permalink}\" title=\"{$page->title}\">{$page->title}</a></li>";
        }
      ?>
    </ul>
    <?php
  endif;

  if ( $tags ):
    ?>
    <h2><?php _e('Tags'); ?></h2>
    <ul>
      <?php
        foreach ( $tags as $tag ) {
          $tag_count = Posts::count_by_tag( $tag->tag, 'published' );
          echo "<li><a href=\"" . URL::get( 'display_entries_by_tag', 'tag=' . $tag->slug ) . "\">{$tag->tag}</a></li>";

        }
      ?>
   </ul>
  <?php endif; ?>

  <h2><?php _e('Meta'); ?></h2>
  <ul>
    <?php
    if ( $loggedin ): ?>
      <li><a href="<?php Site::out_url( 'admin' ); ?>" title="Admin area">Site Admin</a></li>
    <?php else: ?>
      <li><a href="<?php URL::out( 'user', array( 'page' => 'login' ) ); ?>" title="login">Login</a></li>
    <?php endif; ?>

    <li><a href="http://habariproject.org/" title="<?php _e('Powered by Habari'); ?>">Habari</a></li>
    <li><a title="Atom Feed for Posts" href="<?php URL::out( 'atom_feed', array( 'index' => '1' ) ); ?>">Posts Feed</a> </li> 
      <li><a title="Atom Feed for Comments" href="<?php URL::out( 'atom_feed_comments' ); ?>">Comments Feed</a></li>
  </ul>
<?php $theme->area( 'sidebar' ); ?>
</div> <!-- #sidebar -->
<?php Plugins::act( 'theme_sidebar_bottom' ); ?>
<!-- /sidebar -->
