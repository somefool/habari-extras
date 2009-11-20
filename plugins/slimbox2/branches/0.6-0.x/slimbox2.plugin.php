<?php

Class SlimBox2 extends Plugin
{	
	
    public function help()
    {
        $help = _t( '
        Link to an image and add <code>rel="lightbox"</code> to the link.
		ex: <code>&lt;a href="image.jpg" rel="lightbox"&gt;Image&lt;/a&gt;</code>
		For more info check out <a href="http://code.google.com/p/slimbox/">http://code.google.com/p/slimbox/</a>.' );	
        return $help;	
    }
	
	
	/**
	 * Implement the update notification feature
	 */
  	public function action_update_check()
  	{
    	Update::add( 'RecentComments', '41960DE6-2845-11DE-95C0-D3A656D89593',  $this->info->version );
  	}

	public function action_init_theme() {
		Stack::add( 'template_stylesheet', array( URL::get('ajax', array('context' => 'slimbox2_css')), 'screen' ), 'slimbox2' );
        Stack::add('template_header_javascript', Site::get_url('scripts') . '/jquery.js', 'jquery');
		Stack::add('template_header_javascript', $this->get_url() . '/js/slimbox2.js');
	}
 
	public function action_ajax_slimbox2_css($handler) {
		header('Content-type: text/css');
		echo '/* SLIMBOX */		
#lbOverlay {
	position: fixed;
	z-index: 9999;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	background-color: #000;
	cursor: pointer;
}

#lbCenter, #lbBottomContainer {
	position: absolute;
	z-index: 9999;
	overflow: hidden;
	background-color: #fff;
}

.lbLoading {
	background: #fff url( "'.$this->get_url().'/images/loading.gif" ) no-repeat center;
}

#lbImage {
	position: absolute;
	left: 0;
	top: 0;
	border: 10px solid #fff;
	background-repeat: no-repeat;
}

#lbPrevLink, #lbNextLink {
	display: block;
	position: absolute;
	top: 0;
	width: 50%;
	outline: none;
}

#lbPrevLink {
	left: 0;
}

#lbPrevLink:hover {
	background: transparent url( "'.$this->get_url().'/images/prevlabel.gif" ) no-repeat 0 15%;
}

#lbNextLink {
	right: 0;
}

#lbNextLink:hover {
	background: transparent url( "'.$this->get_url().'/images/nextlabel.gif" ) no-repeat 100% 15%;
}

#lbBottom {
	font-family: Verdana, Arial, Geneva, Helvetica, sans-serif;
	font-size: 10px;
	color: #666;
	line-height: 1.4em;
	text-align: left;
	border: 10px solid #fff;
	border-top-style: none;
}

#lbCloseLink {
	display: block;
	float: right;
	width: 66px;
	height: 22px;
	background: transparent url( "'.$this->get_url().'/images/closelabel.gif" ) no-repeat center;
	margin: 5px 0;
	outline: none;
}

#lbCaption, #lbNumber {
	margin-right: 71px;
}

#lbCaption {
	font-weight: bold;
}';
		
	}
	
}

?>