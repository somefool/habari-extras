<?php

class RequireSlug extends Plugin
{ 
	
	/**
	* Add update beacon support
	**/
	public function action_update_check()
	{
		Update::add( $this->info->name, 'f0c38256-b6cb-aa94-bdaf-1e156cc3d8bf', $this->info->version );
	}
	
	/**
	* Change publish form
	**/
	public function action_form_publish($form, $post)
	{
		$form->move_after($form->newslug, $form->content);
		$form->newslug->template= 'admincontrol_text';
		$form->newslug->caption= _t('Slug');
	}

}	

?>