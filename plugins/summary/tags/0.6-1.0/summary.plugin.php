<?php
 
  /**
   * Summary Plugin Class
   * 
   * This plugin adds a 'Summary' field on the entry publishing form, which is
   * available to templates and code as $post->info->summary. A summary element
   * is added to the Atom feed for entries with a non-empty summary.
   *
   **/
 
class SummaryPlugin extends Plugin
{
  /**
   * function info
   * Returns information about this plugin
   * @return array Plugin info array
   **/
  function info()
  {
    return array (
		  'name' => 'Summary',
		  'url' => 'http://scratch.flyoverblues.com/summary.plugin.php',
		  'author' => 'Eli Naeher',
		  'authorurl' => 'http://flyoverblues.com',
		  'version' => '1.0',
		  'description' => 'A plugin for providing post summaries',
		  'license' => 'Apache License 2.0',
		  );
  }
 
  /**
   * Add fields to the publish page for Pages
   *
   * @param FormUI $form The publish form
   * @param Post $post The post being published
   */
  public function action_form_publish($form, $post)
  {
    if($post->content_type == Post::type('entry') ) {
      $form->insert('tags', 'text', 'summary', 'null:null', _t('Summary'), 'admincontrol_textArea');
	  $form->summary->raw = true;
      $form->summary->value = $post->info->summary;
      $form->summary->tabindex = 3;
      $form->tags->tabindex = 4;
      $form->buttons->save->tabindex = 5;
      $form->summary->template = 'admincontrol_text';
    }
  }

  /**
   * Store summary in postinfo table when the entry is published.
   *
   * @param FormUI $form The publish form
   * @param Post $post The post being published
   */

  public function action_publish_post($post, $form)
  {
    $post->info->summary = $form->summary->value;
  }

  /**
   * Add summary element to Atom feed, if a summary is present.
   *
   * @param SimpleXMLElement $feed_entry
   * @param Post $post The post corresponding to the feed entry
   */

  public function action_atom_add_post($feed_entry, $post)
  {
    if ((boolean) $post->info->summary) {
	$feed_entry->addChild( 'summary', $post->info->summary );
      }
  }

}
 
?>