                      <div class="story<?php if($first == true) echo " first" ?>">
                      
                        <h3><a href="<?php echo $post->permalink; ?>" rel="bookmark" title="Permanent Link to <?php echo $post->title; ?>"><?php echo $post->title_out; ?></a></h3>
                        <?php echo $post->content_out; ?>
                        
                        <div class="details">
                          <?php _e('Posted at') ?> <?php echo $post->pubdate->out(); ?> | <a href="<?php echo $post->permalink; ?>#comments" title="Comments on <?php echo $post->title; ?>"><?php echo ($post->comments->approved->count == 0 ? 'No' : $post->comments->approved->count ); ?> <?php echo _n( 'Comment', 'Comments', $post->comments->approved->count ); ?></a> | Filed Under: <?php echo $post->tags_out; ?> <span class="read-on"><a href="<?php echo $post->permalink; ?>">read on</a></span>
                        </div>
                        
                        
                      </div>
                      