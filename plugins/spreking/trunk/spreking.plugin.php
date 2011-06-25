<?php

class Spreking extends Plugin
{
	
	public static $vocab = 'forum_vocabulary';
	public static $anonymity;
	
	/**
	 * Create name string
	 **/
	public function filter_post_type_display($type, $foruse) 
	{ 
		$names = array( 
			'forum' => array(
				'singular' => _t('Forum'),
				'plural' => _t('Forums'),
			),
			'thread' => array(
				'singular' => _t('Thread'),
				'plural' => _t('Threads'),
			)
		); 
 		return isset($names[$type][$foruse]) ? $names[$type][$foruse] : $type; 
	}
	
	/**
	 * Create user permalinks
	 **/
	public function filter_user_permalink($permalink, $user) 
	{ 
		if( $user->loggedin )
		{
			return URL::get( 'view_person', array( 'user' => $user->id ) );
		}
		else
		{
			return URL::get( 'view_person', array( 'user' => 'anonymous' ) );
		}
	}
	
	/**
	 * Change permalink for forums
	 */
	public function filter_post_permalink($permalink, $post) {
		if($post->content_type == Post::type('forum')) {
			return URL::get('subforum_index', array( 'forum' => $post->slug ));
		}
		elseif($post->content_type == Post::type('thread')) {
			$vocab = Vocabulary::get( self::$vocab );
			$thread_term = $vocab->get_term( $post->slug );
			if( $thread_term == NULL) 
			{
				return $permalink;
			}
			return URL::get('forum_view_thread', array( 'forum' => $thread_term->parent()->term, 'thread' => $post->id ));
		}
		elseif($post->content_type == Post::type('reply')) {
			$vocab = Vocabulary::get( self::$vocab );
			$reply_term = $vocab->get_term( $post->slug );
			if( $reply_term == NULL) 
			{
				return $permalink;
			}
			$thread = Post::get( array( 'slug' =>$reply_term->parent()->term ) );
			return URL::get('forum_view_thread', array( 'forum' => $reply_term->parent()->parent()->term, 'thread' => $thread->id )) . '#reply-' . $post->id;
		}
		else {
			return $permalink;
		}
	}
	
	/**
	 * Preserve anonymity for threads/replies
	 */
	public function filter_post_anonymous( $anonymous, $post ) {
		if( $post->content_type == Post::type('thread') || $post->content_type == Post::type('reply') ) {			
			if( self::$anonymity && $post->info->anonymous )
			{				
				return User::anonymous();
			}
			else
			{
				return false;
			}
		}
		else {
			return $anonymous;
		}
	}
	
	/**
	 * Preserve privacy for threads
	 */
	public function filter_post_private( $private, $post ) {
		if( $post->content_type == Post::type('thread') ) {			
			$vocab = Vocabulary::get( self::$vocab );
			$thread_term = $vocab->get_term( $post->slug );
			$forum = Post::get( array( 'slug' => $thread_term->parent()->term ) );
			return (bool) $forum->info->private;
		}
		else {
			return $private;
		}
	}
	
	/**
	 * Create thread creation link for forums
	 */
	public function filter_post_new_thread_link($link, $post) {
		if($post->content_type == Post::type('forum')) {
			return URL::get('forum_new_thread', array( 'forum' => $post->slug ));
		}
		else {
			return $link;
		}
	}
	
	/**
	 * Get thread closure status
	 */
	public function filter_post_closed($closed, $post) {
		if($post->content_type == Post::type('thread')) {
			return $post->info->closed;
		}
		else {
			return $link;
		}
	}
	
	/**
	 * Modify publish form
	 */
	public function action_form_publish($form, $post)
	{
		if ($post->content_type == Post::type('forum')) {
			$header= $form->append('static', 'header', '<div class="container transparent"><strong>Create Forum</strong></div>', 'admincontrol_static');
			$form->move_before($header, $form->title);
			
			$form->title->caption = _t('Name');
			$form->silos->remove();
			$form->content->caption = _t('Description');
			$form->tags->remove();
			
			$form->settings->append('checkbox', 'private', 'null:null', _t('Keep Threads Private'), 'tabcontrol_checkbox');
			$form->settings->private->value = $post->info->private;
			
			$form->minor_edit->remove();
			$form->comments_enabled->caption = _t('Open for Threads');
			$form->pubdate->remove();
		}
	}
	
	/**
	 * Save our data to the database
	 */
	public function action_publish_post( $post, $form )
	{
		if( $post->content_type == Post::type('forum') ) {
			// $this->action_form_publish($form, $post);
			
			$post->info->private= $form->private->value;
		}
	}
	
	/**
	 * Gets all forums the current user can view
	 */
	public static function get_forums()
	{
		$forums = Posts::get( array( 'content_type' => Post::type('forum') ) );
		return $forums;
	}
	
	/**
	 * Gets all threads the current user can view in a certain forum
	 */
	public static function get_threads( $forum )
	{
		
		if( $forum->info->private )
		{
			if( !self::has_permission('view_private_threads', $forum) )
			{
				return array();
			}
		}
		
		$vocab = Vocabulary::get( self::$vocab );
		$forum_term = $vocab->get_term( $forum->slug );

		// Make sure we have a term
		if ( null == $forum_term ) {
			// There's no term for the parent, add it as a top-level term
			$forum_term  = $vocab->add_term( $forum->slug );
		}

		$slugs = array();
		$threads = array();
		$children = $forum_term->children();

		foreach ( $children as $child ) {
			$slugs[] = $child->term;
		}
			
		if( count($slugs) > 0 )
		{
			$threads = Posts::get( array( 'content_type' => Post::type('thread'), 'slug' => $slugs ) );
		}
		
		return $threads;
	}
	
	/**
	 * Gets all replies the current user can view in a certain thread
	 */
	public static function get_replies( $thread )
	{
		$vocab = Vocabulary::get( self::$vocab );
		$thread_term = $vocab->get_term( $thread->slug );

		$slugs = array();
		$replies = array();
		$children = $thread_term->children();

		foreach ( $children as $child ) {
			$slugs[] = $child->term;
		}
			
		if( count($slugs) > 0 )
		{
			$replies = Posts::get( array( 'content_type' => Post::type('reply'), 'slug' => $slugs, 'orderby' => 'pubdate ASC' ) );
		}	
		
		return $replies;
	}
	
	/**
	 * Handle forum_index action
	 */
	public function action_plugin_act_forum_index( $handler )
	{		

		$handler->theme->forums = self::get_forums();
		$handler->theme->display('forum_index');

	}
	
	/**
	 * Handle forum_index action
	 */
	public function action_plugin_act_subforum_index( $handler )
	{
		$forum = Post::get( array( 'slug' => $handler->handler_vars['forum'] ) );
		$handler->theme->forum = $forum;

		$handler->theme->threads = self::get_threads( $forum );
		
		$handler->theme->display('subforum_index');

	}
	
	/**
	 * Helper to handle permissions
	 */
	public static function has_permission( $action, $object = NULL )
	{
		$user = User::identify();
		
		switch( $action )
		{
			case 'create_thread':
				$type = 'post_thread';
				if ( ACL::user_cannot( $user, $type ) || ( ! ACL::user_can( $user, 'post_any', 'create' ) && ! ACL::user_can( $user, $type, 'create') ) ) {
					return false;
				}
				return true;
			case 'reply':
				$type = 'post_reply';
				if ( ACL::user_cannot( $user, $type ) || ( ! ACL::user_can( $user, 'post_any', 'create' ) && ! ACL::user_can( $user, $type, 'create') ) ) {
					return false;
				}
				return true;
			case 'edit_thread':
				$type = 'post_thread';
				if ( ACL::user_cannot( $user, $type ) || ( ! ACL::user_can( $user, 'post_any', 'edit' ) && ! ACL::user_can( $user, $type, 'edit') ) ) {
					return false;
				}
				return true;
			case 'edit_reply':
				$type = 'post_reply';
				if ( ACL::user_cannot( $user, $type ) || ( ! ACL::user_can( $user, 'post_any', 'edit' ) && ! ACL::user_can( $user, $type, 'edit') ) ) {
					return false;
				}
				return true;
			case 'view_private_threads':
				return $user->can('forum_see_private');
			case 'close_thread':
			case 'open_thread':
				return $user->can('forum_close_thread');
			default:
				return false;
		}
		// check the user can create new posts of the set type.
		
		// $type = 'post_thread';
		// if ( ACL::user_cannot( $user, $type ) || ( ! ACL::user_can( $user, 'post_any', 'create' ) && ! ACL::user_can( $user, $type, 'create') ) ) {
		// 	Session::error( _t( 'Creating that post type is denied' ) );
		// 	return _t('<p>You are not authorized to create threads.</p>');
		// }
	}
	
	/**
	 * Handler thread creation form
	 */
	public function filter_handle_thread_creation( $output, $form, $forum )
	{
		if( !self::has_permission('create_thread', $forum) )
		{
			return _t('<p>You are not authorized to create threads.</p>');
		}
				
		$postdata = array(
			'user_id' => User::identify()->id,
			'pubdate' => HabariDateTime::date_create(),
			'content_type' => Post::type('thread'),
			'title' => $form->title->value,
			'content' => $form->content->value,
			'status' => Post::status('published')
		);
		
		$thread = new Post( $postdata );
		$thread->insert();		
		
		if( self::$anonymity && ( $form->anonymous->value == TRUE ) )
		{
			$thread->info->anonymous = TRUE;
		}
		
		// Do vocab stuff
		$vocab = Vocabulary::get( self::$vocab );
		$parent_term = $vocab->get_term( $forum->slug );
		
		if ( null == $parent_term ) {
			// There's no term for the parent, add it as a top-level term
			$parent_term = $vocab->add_term( $forum->slug );
		}
		
		$thread_term = $vocab->add_term( $thread->slug, $parent_term );
		
		$thread->update();
		
		Utils::redirect( $thread->permalink );
		
		return '...';
	}
	
	/**
	 * Handle forum_new_thread action
	 */
	public function action_plugin_act_forum_new_thread( $handler )
	{
				
		$forum = Post::get( array( 'slug' => $handler->handler_vars['forum'] ) );
		$handler->theme->forum = $forum;
		
		if( !self::has_permission('create_thread', $forum) )
		{
			Utils::redirect( $forum->permalink );
		}
		
		// Create the form
		$form = new FormUI( 'create-thread' );
		
		$form->append( 'text', 'title', 'null:null', _t('Title') );
		$form->title->class[] = 'important';
		$form->title->class[] = 'check-change';
		$form->title->tabindex = 1;
		
		$form->append('textarea', 'content', 'null:null', _t('Message'));
		$form->content->class[] = 'resizable';
		$form->content->tabindex = 2;
		$form->content->raw = true;
		
		if( self::$anonymity )
		{
			$form->append( 'checkbox', 'anonymous', 'null:null', _t('Keep this thread anonymous') );
		}
		
		
		$form->append('submit', 'save', _t('Create'));
		$form->save->tabindex = 3;
		
		$form->on_success( 'handle_thread_creation', $forum );
		$handler->theme->form = $form;
		
		$handler->theme->display('new_thread');

	}
	
	/**
	 * Handler thread creation form
	 */
	public function filter_handle_thread_reply( $output, $form, $thread, $forum )
	{
		if( !self::has_permission('reply', $thread) )
		{
			return _t('<p>You are not authorized to reply to this thread.</p>');
		}
						
		$postdata = array(
			'user_id' => User::identify()->id,
			'pubdate' => HabariDateTime::date_create(),
			'content_type' => Post::type('reply'),
			'content' => $form->content->value,
			'status' => Post::status('published')
		);
		
		$reply = new Post( $postdata );
		$reply->insert();
		
		if( self::$anonymity && ( $form->anonymous->value == TRUE ) )
		{
			$reply->info->anonymous = TRUE;
		}
		
		// Do vocab stuff
		$vocab = Vocabulary::get( self::$vocab );
		$parent_term = $vocab->get_term( $thread->slug );
		
		$reply_term = $vocab->add_term( $reply->slug, $parent_term );
		
		$reply->update();
		
		Utils::redirect( $reply->permalink );	
		
		return '...';
	}
	
	/**
	 * Create the reply form
	 */
	public function theme_reply_form( $theme, $thread, $forum )
	{
		if( !self::has_permission('reply', $thread) )
		{
			return _t('<p>You are not authorized to reply to this thread.</p>');
		}
		
		// Create the form
		$form = new FormUI( 'create-reply' );
		
		$form->append('textarea', 'content', 'null:null', _t('Message'));
		$form->content->class[] = 'resizable';
		$form->content->tabindex = 1;
		$form->content->raw = true;
		
		if( self::$anonymity )
		{
			$form->append( 'checkbox', 'anonymous', 'null:null', _t('Keep this reply anonymous') );
		}
		
		$form->append('hidden', 'thread', 'null:null' );
		$form->thread->value = $thread->slug;
		
		$form->append('submit', 'save', _t('Reply'));
		$form->save->tabindex = 3;
		
		
		$form->on_success( 'handle_thread_reply', $thread, $forum );
		$form->out();
	}
	
	/**
	 * Handle forum_view_thread action
	 */
	public function action_plugin_act_forum_view_thread( $handler )
	{
				
		$forum = Post::get( array( 'slug' => $handler->handler_vars['forum'] ) );
		$handler->theme->forum = $forum;
		
		$thread = Post::get( array( 'id' => (int) $handler->handler_vars['thread'] ) );
		$handler->theme->post = $thread;
		$handler->theme->thread = $thread;
		
		// Check permissions for private threads
		if( $thread->private )
		{
			if( !self::has_permission('view_private_threads', $forum) )
			{
				Utils::redirect( URL::get( 'auth', array( 'page' => 'login' ) ) );
			}
		}
		
		$replies = self::get_replies( $thread );
		$handler->theme->replies = $replies;
				
		$handler->theme->display('thread');

	}
	
	/**
	 * Handle forum_close_thread action
	 */
	public function action_plugin_act_forum_close_thread( $handler )
	{		
	
		$thread = Post::get( array( 'id' => (int) $handler->handler_vars['thread'] ) );
		
		if( !self::has_permission('close_thread', $thread))
		{
			Utils::redirect( $thread->permalink );
			exit;
		}
		
		$thread->info->closed = true;
		
		$thread->update();
		
		Utils::redirect( $thread->permalink );
				
	}
	
	/**
	 * Handle forum_open_thread action
	 */
	public function action_plugin_act_forum_open_thread( $handler )
	{		
	
		$thread = Post::get( array( 'id' => (int) $handler->handler_vars['thread'] ) );
		
		if( !self::has_permission('open_thread', $thread))
		{
			Utils::redirect( $thread->permalink );
			exit;
		}
		
		$thread->info->closed = false;
		
		$thread->update();
		
		Utils::redirect( $thread->permalink );
				
	}
	
	/**
	 * Create rewrite rule
	 */
	public function action_init()
	{
		// self::uninstall();
		// self::install();
		
		if( Options::get( 'spreking__anonymity' ) == TRUE )
		{
			self::$anonymity = true;
		}
		else
		{
			self::$anonymity = false;
		}
		
		$this->add_template('forum_index', dirname(__FILE__) . '/forum_index.php');
		$this->add_template('subforum_index', dirname(__FILE__) . '/subforum_index.php');
		$this->add_template('new_thread', dirname(__FILE__) . '/new_thread.php');
		$this->add_template('thread', dirname(__FILE__) . '/thread.php');

		$this->add_rule('"forum"', 'forum_index');
		$this->add_rule('"forum"/forum/"new"', 'forum_new_thread');
		$this->add_rule('"forum"/forum/thread/"close"', 'forum_close_thread');
		$this->add_rule('"forum"/forum/thread/"open"', 'forum_open_thread');
		$this->add_rule('"forum"/forum/thread', 'forum_view_thread');
		$this->add_rule('"forum"/forum', 'subforum_index');
		$this->add_rule('"person"/user', 'view_person');
	}
	
	/**
	 * Define configuration form
	 */
	public function configure()
	{
		$ui = new FormUI( 'spreking_config' );
		$ui->append( 'checkbox', 'anonymity', 'option:spreking__anonymity', _t('Enable anonymous threads & replies') );
		$ui->append('submit', 'save', _t('Save'));
		return $ui;
	}
	
	/**
	 * Set up needed permissions
	 */
	private static function install()
	{
		// Create post types
		Post::add_new_type( 'forum' );
		Post::add_new_type( 'thread' );
		Post::add_new_type( 'reply' );
		
		// Create vocabulary
		$params = array(
			'name' => self::$vocab,
			'description' => 'A vocabulary for describing relationships between threads',
			'features' => array( 'hierarchical' )
		);
		
		$vocabulary = new Vocabulary( $params );
		$vocabulary->insert();
		
		// Create tokens
		ACL::create_token( 'forum_see_private', _t('See Private Threads'), 'Forum', false );
		ACL::create_token( 'forum_close_thread', _t('Close Threads'), 'Forum', false );
		
		// Grant tokens
		$group = UserGroup::get_by_name( 'admin' );
		$group->grant( 'forum_close_thread' );
		$group->grant( 'forum_see_private' );
	}
	
	/**
	 * Set up needed permissions
	 */
	private static function uninstall()
	{
		// Deactivate post types
		Post::deactivate_post_type( 'thread' );
		Post::deactivate_post_type( 'forum' );
		
		// Remove tokens
		ACL::destroy_token( 'forum_close_thread' );
		ACL::destroy_token( 'forum_see_private' );
	}
	
	public function action_plugin_activation( $file )
	{
		if ( $file == str_replace( '\\','/', $this->get_file() ) ) {
			self::install();
		}
	}

	public function action_plugin_deactivation( $file )
	{
		if ( $file == str_replace( '\\','/', $this->get_file() ) ) {
			self::uninstall();
		}
	}
}

?>