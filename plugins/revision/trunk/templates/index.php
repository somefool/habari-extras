<?php $theme->display('header'); ?>
<div class="container">
  <div class="item clear">
    <div class="column span-3">&nbsp;</div>
    <div class="column span-8 last"><?php $post->updated->out('F jS, Y H:i:s'); ?></div>
  </div>

  <div class="item clear">
    <div class="column span-3">
      <label><?php echo _t( 'Title' ); ?></label>
    </div>
    <div class="column span-8 last">
      <?php echo $post->title; ?>
    </div>
  </div>

  <div class="item clear">
    <div class="column span-3">
      <label><?php echo _t( 'Author' ); ?></label>
    </div>
    <div class="column span-8 last">
      <?php echo $post->author->username; ?>
    </div>
  </div>

  <div class="item clear">
    <div class="column span-3">
      <label><?php echo _t( 'Content' ); ?></label>
    </div>
    <div class="column span-8 last">
      <?php echo $post->content; ?>
    </div>
  </div>
</div>

<form action="<?php URL::out( 'admin', 'page=revision_diff' ); ?>" method="get">

<div class="container">
<?php foreach ( $revisions as $i => $revision ): ?>
  <div class="item clear">
    <div class="head">
      <span class="checkboxandtitle">
        <?php if ( $i == 0 ): ?>
        <input type="radio" name="old_revision_id" value="<?php echo $revision->id; ?>" disabled="disabled" />
        <?php else: ?>
        <input type="radio" name="old_revision_id" value="<?php echo $revision->id; ?>" />
        <?php endif; ?>
        <input type="radio" name="new_revision_id" value="<?php echo $revision->id; ?>" />
        <span class="title"><a href="<?php URL::out( 'admin', 'page=revision&revision_id=' . $revision->id ); ?>"><?php $revision->modified->out('H:i:s, F jS, Y'); ?></a> by <?php echo $revision->author->username; ?></span>
      </span>
      <?php if ( $i != 0 ): ?>
      <ul class="dropbutton">
        <li><a href="<?php URL::out( 'admin', 'page=revision&action=rollback&revision_id=' . $revision->id ); ?>"><?php echo _t( 'Rollback' ); ?></a></li>
      </ul>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
</div>

<div class="container transparent">
  <div class="item controls">
    <input type="submit" value="<?php echo _t( 'Compare selected revisions' ); ?>" class="submitbutton" />
  </div>
</div>

</form>

<?php $theme->display('footer'); ?>
