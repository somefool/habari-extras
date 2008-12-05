<?php
class FreeStyleAdmin extends Plugin {
    function info() {
        return array(
            'url'            => 'http://iamgraham.net/plugins',
            'name'            => 'FreeStyleAdmin',
            'description'    => 'Allows you to load arbitrary CSS files into the admin pages.',
            'license'        => 'Apache License 2.0',
            'author'        => 'Graham Christensen',
            'authorurl'        => 'http://iamgraham.net/',
            'version'        => '0.1'
        );
    }

    public function filter_plugin_config($actions, $plugin_id) {
        if ($plugin_id == $this->plugin_id()) {
            $actions[] = _t('Configure');
        }
        return $actions;
    }

    public function action_plugin_ui($plugin_id, $action) {

        if ($plugin_id == $this->plugin_id()) {
            switch ($action) {
                case _t('Configure'):
                    $ui = new FormUI(strtolower(get_class($this)));
                    $clientcode    = $ui->append('text', 'css_location', 'option:freestyleadmin__css_location', _t('FreeStyle CSS Location', 'FreeStyleAdmin'));
                    
                    $ui->on_success(array($this, 'updated_config'));
                    $ui->append( 'submit', 'save', _t( 'Save', 'FreeStyleAdmin' ) );
                    $ui->set_option( 'success_message', _t( 'Configuration saved', 'FreeStyleAdmin' ) );
                    $ui->out();
                break;
            }
        }
    }

    public function updated_config($ui) {
        return true;
    }

    private static function getvar($var) {
        return Options::get('freestyleadmin__'.$var);
    }

    function action_admin_header() {
		if (self::getvar('css_location') != '') {
			Stack::add('admin_stylesheet', array(self::getvar('css_location'), 'screen'));
		}
    }

}
?>

