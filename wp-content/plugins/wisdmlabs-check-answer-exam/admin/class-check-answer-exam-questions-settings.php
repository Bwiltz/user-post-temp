<?php
namespace wdmcheckanswerexam;

    /**
    *Function to add the LD pages and custom setting for the quizzes.
    */
class addLdTab
{
    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'addQuizMetaBox'));
        add_action('save_post_sfwd-quiz', array($this, 'saveQuizMetaBoxData'));
    }

    public function addQuizMetaBox()
    {
        add_meta_box(
            'compulsory_check_answer',
            __('Retake Question', 'wdm_cae'),
            array($this, 'renderMetaBoxSettingHTML'),
            array('sfwd-quiz'),
            'side',
            'default'
        );
    }

    public function renderMetaBoxSettingHTML($post)
    {
        $post_id = $post->ID;
        include __DIR__.'/templates/compulsory_check_answer_HTML.php';
    }

    public function saveQuizMetaBoxData($post_id)
    {
        // Bail if we're doing an auto save
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        // if our nonce isn't there, or we can't verify it, bail
        if (!isset($_POST['compulsory_check_answer_nonce']) || !wp_verify_nonce($_POST['compulsory_check_answer_nonce'], 'compulsory_check_answer_action')) {
            return;
        }
        /* saving data of 'option-renumbering' */
        $check = isset($_POST['wdm_check_answer']) ? 1 : 0;
        update_post_meta($post_id, 'wdm_check_answer', $check);
    }
}
new addLdTab();
