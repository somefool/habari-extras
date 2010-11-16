<?php
class PostURL extends Plugin {

  public function action_form_publish($form, $post) {
    if($form->content_type->value == Post::type('entry') || $form->content_type->value == Post::type('page')) {
      $form->append('text', 'url', 'null:null', _t('URL'), 'admincontrol_text');
      $form->url->tabindex = 2;
      $form->url->value = $post->info->url;
      $form->url->move_after($form->title);
    }
  }

  function action_publish_post($post, $form) {
    if($post->content_type == Post::type('entry') || $post->content_type == Post::type('page')) {
      $post->info->url = $form->url->value;
    }
  }

  public function filter_post_permalink($permalink,$post) {
   if(is_null($post->info->url)) {
     return $permalink;
   } else {
     return $post->info->url;
   }
  }
 
}
?>
