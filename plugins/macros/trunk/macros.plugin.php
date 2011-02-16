<?php
class Macros extends Plugin {

    const   GUID         = 'a59c173e-6b2f-4917-afef-616fd8d615f4';
    const   PLUGIN_NAME  = 'Macros';
    const   PLUGIN_TOKEN = 'Macros_token';
    const   VERSION      = '0.1alpha';

    const   MARKER_OPEN  = '<!--+';
    const   MARKER_CLOSE = '+-->';


    /*
     * Split the string into tokens the way the shell would.
     * Adapted shamelessly (but with enormous respect and gratitude) from
     * Perl 5.8.8's Test::ParseWords::old_shellwords function.
     */
    public function shellsplit($text_p='') {
        $text = trim($text_p);
        $a_tokens = array();
        /*
         * The regexes are fairly complex, so let's predefine them to keep
         * the logic readable.
         */
        $p = array(
            /*
             * "-quoted string
             */
            '^"(([^"\\\\]|\\\\.)*)"',
            /*
             * Unterminated "-quoted string
             */
            '^"',
            /*
             * '-quoted string
             */
            "^'(([^'" . '\\\\]|\\\\.)*)' . "'",
            /*
             * Unterminated '-quoted string
             */
            "^'",
            /*
             * Explicit slosh followed by anything
             */
            '^\\\\(.)',
            /*
             * String of non-whitespace, non-slosh, non-quote charactersa
             */
            '^([^\s\\\\' . "'" . '"]+)',
            );
    
        while ($text) {
            $field = '';
            while (true) {
                if (preg_match('#' . $p[0] . '#', $text, $bits)) {
                    $text = preg_replace('#' . $p[0] . '#',
                                         '', $text, 1);
                    $snippet = preg_replace('#\\\\(.)#', '\1', $bits[1]);
                }
                else if (preg_match('#' . $p[1] . '#', $text)) {
                    trigger_error("Unmatched double quote: $text\n",
                                  E_USER_WARNING);
                    return array();
                }
                else if (preg_match('#' . $p[2] . '#', $text, $bits)) {
                    $text = preg_replace('#' . $p[2] . '#', '', $text);
                    $snippet = preg_replace('#\\\\(.)#', '\1', $bits[1]);
                }
                else if (preg_match('#' . $p[3] . '#', $text)) {
                    trigger_error("Unmatched single quote: $text\n",
                                  E_USER_WARNING);
                    return array();
                }
                else if (preg_match('#' . $p[4] . '#', $text, $bits)) {
                    $text = preg_replace('#' . $p[4] . '#', '', $text, 1);
                    $snippet = $bits[1];
                }
                else if (preg_match('#' . $p[5] . '#', $text, $bits)) {
                    $text = preg_replace('#' . $p[5] . '#', '', $text, 1);
                    $snippet = $bits[1];
                }
                else {
                    $text = trim($text);
                    break;
                }
                $field .= $snippet;
            }
            $a_tokens[] = $field;
        }
        return $a_tokens;
    }

    /*
     * Stuff to be added to the document header when we're doing an
     * admin/configuration panel.
     */
    public function action_admin_header($theme) {
        $vars = Controller::get_handler_vars();
        $js_xid2def = '';
        if (($theme->page == 'plugins')
            && isset($vars['configure'])
            && ($this->plugin_id == $vars['configure'])) {
            Stack::add('admin_stylesheet',
                       array($this->get_url() . '/macros.css',
                             'screen'),
                       'macros',
                       array('admin'));
            $macros = DB::get_results('SELECT * FROM ' . DB::table('macro')
                                       . ' ORDER BY name ASC');
            $js_xid2def .= "\n  \$(document).ready(function(){\n"
                .   "    mDefs = [];\n"
                .   "    mDefs[0] = ['', false, false, 0, '', ''];\n";
            foreach ($macros as $macro) {
                $xid = $macro->xid;
                $nargs = $macro->nargs;
                $enabled = $macro->enabled;
                $name = HTMLentities("'" . $macro->name . "'");
                $desc = HTMLentities("'" . $macro->description . "'");
                $def = HTMLentities("'" . $macro->definition . "'");
                $container = $macro->container ? 'true' : 'false';
                $eval = $macro->eval ? 'true' : 'false';
                $js_xid2def .= "    mDefs[$xid] = ["
                    . $name . ', '         /* [0] */
                    . $enabled . ', '      /* [1] */
                    . $container . ', '    /* [2] */
                    . $eval . ', '         /* [3] */
                    . $nargs . ', '        /* [4] */
                    . $desc . ', '         /* [5] */
                    . $def                 /* [6] */
                    . "];\n";
            }
            $js_xid2def .= "    \$('#mName select')"
                . ".change(function(){\n"
                . "      mNum = \$(this).val();\n"
                . "      \$('#mEnabled input[type=\"checkbox\"]')"
                . ".attr('checked', mDefs[mNum][1]);\n"
                . "      \$('#mDefinition input').val($('<input value=\"' + mDefs[mNum][6] + '\"/>').val());\n"
                . "      \$('#mContainer input[type=\"checkbox\"]')"
                . ".attr('checked', mDefs[mNum][2]);\n"
                . "      \$('#mEval input[type=\"checkbox\"]')"
                . ".attr('checked', mDefs[mNum][3]);\n"
                . "      \$('#mNargs input[type=\"text\"]')"
                . ".val($('<input value=\"' + mDefs[mNum][4] + '\"/>').val());\n"
                . "      \$('#mDescription input[type=\"text\"]')"
                . ".val($('<input value=\"' + mDefs[mNum][5] + '\"/>').val());\n"
                . "      \$('#mDefinition input[type=\"text\"]')"
                . ".val($('<input value=\"' + mDefs[mNum][6] + '\"/o>').val())});\n"
                . "  })\n";
            Stack::add('admin_header_javascript',
                       $js_xid2def,
                       'macros',
                       'admin');
        }
    }

    public function action_update_check() {
        Update::add(self::PLUGIN_NAME, self::GUID, $this->info->version);
    }

    /*
     * Admin-type methods
     */
    public function action_plugin_activation($file) {
        DB::register_table('macro');
        /*
         * Create the database table, or upgrade it
         */
        $dbms = DB::get_driver_name();
        $sql = 'CREATE TABLE ' . DB::table('macro') . ' '
            . '(';
        if ($dbms == 'sqlite') {
            $sql .= 'xid INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, ';
        }
        else if ($dbms == 'mysql') {
            $sql .= 'xid INT(9) NOT NULL AUTO_INCREMENT, '
                . 'UNIQUE KEY xid (xid), ';
        }
        else {
            $sql .= 'xid INT(9) NOT NULL AUTO_INCREMENT, '
                . 'UNIQUE KEY xid (xid), ';
        }
        $sql .= 'name VARCHAR(255), '
            . 'enabled INTEGER DEFAULT 1, '
            . 'modified INTEGER, '
            . 'description TEXT, '
            . 'definition TEXT NOT NULL, '
            . 'container INTEGER DEFAULT 0, '
            . 'nargs INTEGER DEFAULT 0, '
            . 'eval INTEGER DEFAULT 0'
            . ')';
        if (! DB::dbdelta($sql)) {
//            Utils::debug(DB::get_errors());
        }
        if ($file == str_replace('\\', '/', $this->get_file())) {
            ACL::create_token(self::PLUGIN_TOKEN,
                              _t('Allow use of Macros plugin'),
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
            $macros = DB::get_results('SELECT * FROM ' . DB::table('macro')
                                      . ' ORDER BY name ASC');
            switch ($action) {
            case _t('Configure'):
                $ui = new FormUI(strtolower(get_class($this)));
                $ui->append('checkbox',
                            'nElideDisabled',
                            '1',
                            _t('Elide references to disabled macros'));
                $ui->append('fieldset', 'setAdd', 'Add a macro');
                $setAdd = $ui->setAdd;

                /*
                 * Fields to add a macro.
                 */
                $setAdd->append(new FormControlText('nName',
                                                    null,
                                                    _t('Macro name:')));
                $setAdd->nName->class = 'block';
                $setAdd->nName->value = '';

                $setAdd->append('checkbox',
                                'nEnabled',
                                '1',
                                _t('Macro is enabled'));

                $setAdd->append(new FormControlText('nDescription',
                                                    null,
                                                    _t('Description:')));
                $setAdd->nDescription->class = 'block';
                $setAdd->nDescription->value = '';
                $setAdd->nDescription->size = 50;

                $setAdd->append('textarea',
                                'nDefinition',
                                null,
                                _t('Macro definition:'));
                $setAdd->nDefinition->value = '';
//                $setAdd->nDefinition->size = 64;

                $setAdd->append('fieldset',
                                'setAddAdvanced',
                                'Advanced options');
                $setAdv = $setAdd->setAddAdvanced;

                $setAdv->append(new FormControlText('nNargs',
                                                    '0',
                                                    _t('Number of arguments:')));
                $setAdv->nNargs->size = 2;
                $setAdv->nNargs->value = '0';
// <a class="help" href="http://ken.coar.org/habari/admin/plugins?configure=a51b1bc8&configaction=Configure&help=_help">?</a>


                $setAdv->append('checkbox',
                                'nContainer',
                                '1',
                                _t('Is a container (has start and end tags)'));

                $setAdv->append('checkbox',
                                'nEval',
                                '1',
                                _t('Evaluable (definition contains '
                                   . 'PHP code to be evaluated)'));

                /*
                 * Only allow editing and deletion if we already have
                 * some macros defined.
                 */
                if ($n = count($macros)) {
                    $anames = array('' => 0);
                    $adefs = array();
                    foreach ($macros as $macro) {
                        $anames[$macro->name] = $macro->xid;
                        $adefs[$macro->name] = $macro->definition;
                    }

                    /*
                     * First, the modification stuff.
                     */
                    $ui->append('fieldset', 'setModify',
                                'Modify a macro');
                    $setModify = $ui->setModify;

                    $setModify->append(new FormControlSelect('mName',
                                                             null,
                                                             _t('Select macro to modify')));
                    $setModify->mName->size = 1;
                    $setModify->mName->options = array_flip($anames);

                    $setModify->append('checkbox',
                                       'mEnabled',
                                       '1',
                                       _t('Macro is enabled'));
                    if ($aDefs[$setModify->mName->value]->enabled) {
                        $setModify->mEnabled->checked = 'checked';
                    }

                    $setModify->append(new FormControlText('mDescription',
                                                           null,
                                                           _t('Description:')));
                    $setModify->mDescription->class = 'block';
                    $setModify->mDescription->value = '';
                    $setModify->mDescription->size = 50;

                    $setModify->append(new FormControlText('mDefinition',
                                                           null,
                                                           _t('Definition:')));
                    $setModify->mDefinition->class = 'block';
                    $setModify->mDefinition->value = '';
                    $setModify->mDefinition->size = 50;

                    $setModify->append('fieldset',
                                       'setModAdvanced',
                                       'Advanced options');
                    $setAdv = $setModify->setModAdvanced;
                    $setAdv->append('checkbox',
                                    'mContainer',
                                    '1',
                                    _t('Is a container'));

                    $setAdv->append('checkbox',
                                    'mEval',
                                    '1',
                                    _t('Is evaluable'));

                    $setAdv->append(new FormControlText('mNargs',
                                                        null,
                                                        _t('Argument count:')));
                    $setAdv->mNargs->size = 2;
                    $setAdv->mNargs->value = '0';

                    /*
                     * Now the deletion stuff.
                     */
                    $ui->append('fieldset', 'setDelete',
                                'Delete macros');
                    $setDelete = $ui->setDelete;
                    $setDelete->class = 'cbox-left';

                    foreach ($anames as $name => $xid) {
                        if (! $xid) {
                            continue;
                        }
                        $setDelete->append('checkbox',
                                           'macro_' . $xid,
                                           $xid,
                                           $name);
                    }
                }

                $ui->append('submit', 'save', 'Save');
                $ui->on_success(array($this, 'handle_config_form'));
                $ui->out();
                break;
            }
        }
    }

    /*
     * An Admin Form Hath Been Submitted-eth.
     */
    public function handle_config_form($ui) {
        $macros = DB::get_results('SELECT * FROM ' . DB::table('macro')
                                   . ' ORDER BY name ASC');
        $macroById = array();
        foreach ($macros as $macro) {
            $macrosById[$macro->xid] = $macro;
        }

        $setAdd = $ui->setAdd;
        $setAddAdv = $setAdd->setAddAdvanced;
        $setMod = $ui->setModify;
        $setModAdv = $setMod->setModAdvanced;
        $setDel = $ui->setDelete;

        if ($name = $ui->nName->value) {
            $desc = $setAdd->nDescription->value;
            $desc = html_entity_decode($desc);
            $def = $setAdd->nDefinition->value;
            $def = html_entity_decode($def);
            $nargs = $setAddAdv->nNargs->value;
            $container = $setAddAdv->nContainer->value ? 1 : 0;
            $eval = $setAddAdv->nEval->value ? 1 : 0;
            DB::insert(DB::table('macro'),
                       array('name'        => $name,
                             'description' => $desc,
                             'definition'  => $def,
                             'nargs'       => $nargs,
                             'container'   => $container,
                             'eval'        => $eval));
//            Utils::debug(DB::get_errors());
        }

        /*
         * Modify an abbreviation.
         */
        $xid = $setMod->mName->value;
        if ($xid) {
            $desc = $setMod->mDescription->value;
            $desc = html_entity_decode($desc);
            $def = $setMod->mDefinition->value;
            $def = html_entity_decode($def);
            $nargs = $setModAdv->mNargs->value;
            $container = $setModAdv->mContainer->value ? 1 : 0;
            $eval = $setModAdv->mEval->value ? 1 : 0;
//           Utils::debug(array($def, $prefix, $postfix, $abbrevById[$xid]));
            if (($def != $macrosById[$xid]->definition)
                || ($desc != $macrosById[$xid]->description)
                || ($nargs != $abbrevById[$xid]->nargs)
                || ($container != $macrosById[$xid]->container)
                || ($eval != $macrosById[$xid]->eval)) {
                DB::update(DB::table('macro'),
                           array('definition'  => $def,
                                 'description' => $desc,
                                 'nargs'       => $nargs,
                                 'container'   => $container,
                                 'eval'        => $eval),
                           array('xid'         => $xid));
//            Utils::debug(DB::get_errors());
            }
        }
        
        /*
         * Delete some?
         */
        $a_delboxes = $setDel->controls;
        foreach ($a_delboxes as $id => $formctl) {
            if ($formctl->value) {
                preg_match('/^macro_(\d+)$/', $id, $pieces);
                DB::delete(DB::table('macro'), array('xid' => $pieces[1]));
//                Utils::debug(DB::get_errors());
            }
        }

        $ui->save;
        return false;
        }

    public function action_init() {
        DB::register_table('macro');
        Stack::add('template_stylesheet',
                   array($this->get_url() . '/macros.css',
                         'screen'),
                   'macros');
    }

    /*
     * Do the actual replacement of any macros.  Don't make any
     * changes to text inside tags!
     */
    public function filter_post_content_out($content, $post) {
        $macros = DB::get_results('SELECT * FROM ' . DB::table('macro'));
        $content = " $content ";
        $redelim = Utils::regexdelim( $content );
        /*
         * Save any markup tags so we don't insert into the
         * middle of one.
         */
        $regex = $redelim . '(<[^!+][^>]*>)' . $redelim . 'siS';
        $saved_markup = array();
        while (preg_match($regex, $content, $matched)) {
            $saved_markup[] = $matched[1];
            $pattern = sprintf('%s\Q%s\E%ss',
                               $redelim,
                               $matched[1],
                               $redelim);
            $content = preg_replace($pattern,
                                    "<!-- SAVED_MARKUP -->"
                                    . count($saved_markup)
                                    . "<!-- /SAVED_MARKUP -->",
                                    $content);
        }

        $raw = $content;
        /*
         * Process the macros in turn.  Restart from scratch after any
         * replacements, to allow inner references of one macro by another.
         */
        while (true) {
            /*
             * Keep track of how many of the macros we've done.
             */
            $mNum = 0;
            foreach ($macros as $macro) {
                $mNum++;
                $prefix = sprintf('%s\Q%s\E\s*\Q%s\E',
                                  $redelim, Macros::MARKER_OPEN,
                                  $macro->name);
                if ($macro->nargs) {
                    $prefix .= '\s+(.*?)';
                }
                $prefix .= sprintf('\s*\Q%s\E', Macros::MARKER_CLOSE);
                $postfix = $redelim . 's';
                if ($macro->container) {
                    $prefix = sprintf('%s(.*?)\Q%s\E\s*/%s\s*\Q%s\E',
                                      $prefix, Macros::MARKER_OPEN,
                                      $macro->name, Macros::MARKER_CLOSE);
                }
                $pattern = $prefix . $postfix;
//                Utils::debug('pattern=|' . $pattern . '|');
                /*
                 * If we don't have a match, move on to the next macro.
                 */
                if (! preg_match($pattern, $raw, $matched)) {
                    continue;
                }

                /*
                 * Position of the next matched group expression.
                 */
                $mPos = 1;
                /*
                 * Got a match!  Do it!  Exactly what we do is dependent on the
                 * macro definition..
                 */
                $replacement = $macro->definition;
                if ($macro->nargs) {
                    $args = array_slice($this->shellsplit($matched[$mPos++]),
                                        0, $macro->nargs);
                    $replacement = vsprintf($replacement, $args);
                }
                if ($macro->eval) {
                    $replacement = eval('?' . '>' . $replacement);
                }
                $repregex = sprintf('%s\Q%s\E%sms',
                                    $redelim,
                                    $matched[0],
                                    $redelim);
                if ($macro->container) {
                    /*
                     * Do something special if we have a container
                     * macro; the content is treated specially.
                     */
                }
//                Utils::debug(array($repregex, $replacement, $raw));
                $raw = preg_replace($repregex, $replacement, $raw, 1);
//                Utils::debug(array($repregex, $replacement, $raw));
                 /*
                 * Return to the outer loop (i.e., restart with the first
                 * macro again, in case this insertion added some).
                 */
                break;
            }
            /*
             * Skip out if we've gone through all the macros.
             */
            if ($mNum >= count($macros)) {
                break;
            }
        }

        /*
         * Done with the macros; now restore any saved markup.
         */
        for ($i = 1; $i <= count($saved_markup); $i++) {
            $regex = sprintf('%s\Q<!-- SAVED_MARKUP -->'
                             . '%s'
                             . '<!-- /SAVED_MARKUP -->\E%ss',
                             $redelim, $i, $redelim);
            $raw = preg_replace($regex,
                                $saved_markup[$i - 1],
                                $raw);
        }
        $content = trim($raw);
        return $content;
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
