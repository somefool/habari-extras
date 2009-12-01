<?php

require_once 'vendor/Akismet.class.php';

class HabariAkismet extends Plugin
{
    const SERVER_AKISMET = 'rest.akismet.com';
    const SERVER_TYPEPAD = 'api.antispam.typepad.com';

    public function info()
    {
        return array(
            'url' => 'http://andrewhutchings.com/projects',
            'name' => 'Habari Akismet',
            'description' => 'Provides the Akismet and TypePad AntiSpam spam filter webservices to Habari comments.',
            'license' => 'Apache License 2.0',
            'author' => 'Andrew Hutchings',
            'authorurl' => 'http://andrewhutchings.com/',
            'version' => '0.1.1'
        );
    }

    public function action_plugin_activation($file)
    {
        if (realpath($file) == __FILE__) {
            Session::notice(_t('Please set your Akismet or TypePad AntiSpam API Key in the configuration.'));
        }
    }

    public function filter_plugin_config($actions, $plugin_id)
    {
        if ($plugin_id == $this->plugin_id()) {
            $actions[] = _t('Configure');
        }

        return $actions;
    }

    public function action_plugin_ui($plugin_id, $action)
    {
        if ($plugin_id == $this->plugin_id()) {
            switch ($action) {
                case _t('Configure'):

                    $form = new FormUI(strtolower(get_class($this)));
                    $form->append('select', 'provider', 'habariakismet__provider', _t('Service'));
                    $form->provider->options = array(
                        'Akismet' => 'Akismet',
                        'TypePad AntiSpam' => 'TypePad AntiSpam'
                    );
                    $api_key = $form->append('text', 'api_key', 'habariakismet__api_key', _t('API Key'));
                    $api_key->add_validator('validate_required');
                    $api_key->add_validator(array($this, 'validate_api_key'));
                    $form->append('submit', 'save', 'Save');
                    $form->out();
                    break;
            }
        }
    }

    public function validate_api_key($key, $control, $form)
    {
        $endpoint = ($form->provider->value == 'Akismet') ? self::SERVER_AKISMET : self::SERVER_TYPEPAD;

        $a = new Akismet(Site::get_url('habari'), $key);
        $a->setAkismetServer($endpoint);

        if (!$a->isKeyValid()) {
            return array(sprintf(_t('Sorry, the %s API key %s is <b>invalid</b>. Please check to make sure the key is entered correctly.'), $form->provider->value, $key));
        }

        return array();
    }

    public function set_priorities()
    {
        return array(
            'action_comment_insert_before' => 1
        );
    }

    public function action_comment_insert_before(Comment $comment)
    {
        $api_key  = Options::get('habariakismet__api_key');
        $provider = Options::get('habariakismet__provider');

        if ($api_key == null || $provider == null) {
            return;
        }

        $endpoint = ($provider == 'Akismet') ? self::SERVER_AKISMET : self::SERVER_TYPEPAD;

        $a = new Akismet(Site::get_url('habari'), $api_key);
        $a->setAkismetServer($endpoint);
        $a->setCommentAuthor($comment->name);
        $a->setCommentAuthorEmail($comment->email);
        $a->setCommentAuthorURL($comment->url);
        $a->setCommentContent($comment->content);
        $a->setPermalink($comment->post->permalink);

        try {
            $comment->status = ($a->isCommentSpam()) ? 'spam' : 'ham';
            return;
        } catch (Exception $e) {
            EventLog::log($e->getMessage(), 'notice', 'comment', 'HabariAkismet');
        }
    }

    public function action_admin_moderate_comments($action, $comments, $handler)
    {
        $false_negatives = 0;
        $false_positives = 0;

        $provider = Options::get('habariakismet__provider');
        $endpoint = ($provider == 'Akismet') ? self::SERVER_AKISMET : self::SERVER_TYPEPAD;

        $a = new Akismet(Site::get_url('habari'), Options::get('habariakismet__api_key'));
        $a->setAkismetServer($endpoint);

        foreach ($comments as $comment) {
            switch ($action) {
                case 'spam':
                    if ($comment->status == Comment::STATUS_APPROVED || $comment->status == Comment::STATUS_UNAPPROVED) {
                        $a->setCommentAuthor($comment->name);
                        $a->setCommentAuthorEmail($comment->email);
                        $a->setCommentAuthorURL($comment->url);
                        $a->setCommentContent($comment->content);

                        $a->submitSpam();

                        $false_negatives++;
                    }

                    break;
                case 'approved':
                    if ($comment->status == Comment::STATUS_SPAM) {
                        $a->setCommentAuthor($comment->name);
                        $a->setCommentAuthorEmail($comment->email);
                        $a->setCommentAuthorURL($comment->url);
                        $a->setCommentContent($comment->content);

                        $a->submitHam();

                        $false_positives++;
                    }

                    break;
            }
        }

        if ($false_negatives) {
            Session::notice(_t('Reported %d false negatives to %s.', array($false_negatives, $provider)));
        }

        if ($false_positives) {
            Session::notice(_t('Reported %d false positives to %s.', array($false_positives, $provider)));
        }
    }
}

?>
