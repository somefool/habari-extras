<?php
class AtomIcon extends Plugin {

    const PLUGIN_TOKEN = 'AtomIcon_token';

    /**
     * Do any wrapper-like things to the Atom feed proper.
     * @param SimpleXMLElement $xml the Atom feed document
     * @return SimpleXMLElement the modified Atom feed document
     */
    public function action_atom_create_wrapper( $xml )
    {
        if ($iconurl = Options::get('atomicon_iconurl')) {
            $xml->addChild('icon', $iconurl);
        }
        return $xml;
    }

    /*
     * Admin-type methods
     */
    public function action_plugin_activation($file) {
        if ($file == str_replace('\\', '/', $this->get_file())) {
            ACL::create_token(self::PLUGIN_TOKEN,
                              _t('Allow use of AtomIcon plugin'),
                              'Category',
                              false);
            $group = UserGroup::get_by_name('admin');
            $group->grant(self::PLUGIN_TOKEN);
        }
    }

    public function action_plugin_deactivation($file) {
        if ($file == str_replace('\\', '/', $this->get_file())) {
            ACL::destroy_token(self::PLUGIN_TOKEN);
        }
    }

    /*
     * Add a configuration panel for us.
     */
    public function filter_plugin_config($actions, $plugin_id) {
        if ($plugin_id == $this->plugin_id()) {
            $actions[] = _t('Configure');
        }
        return $actions;
    }
    /*
     * And here's the actual configuration panel itself.
     */

    public function action_plugin_ui($plugin_id, $action) {
        if ($plugin_id == $this->plugin_id()) {
            switch ($action) {
            case _t('Configure'):
                $ui = new FormUI(strtolower(get_class($this)));
                $ui->append('text',
                            'iconurl',
                            'option:atomicon_iconurl',
                            _t('Feed icon URL:'));
                $ui->iconurl->size = 64;
                $ui->append('submit', 'save', 'Save');
                $ui->out();
                break;
            }
        }
    }

}
/*
 * Local Variables:
 * mode: C
 * c-file-style: "bsd"
 * tab-width: 4
 * indent-tabs-mode: nil
 * End:
 */
?>
