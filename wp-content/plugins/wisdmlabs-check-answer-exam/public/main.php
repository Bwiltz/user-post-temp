<?php

// include_once 'class-createQuiz.php';

include_once(ABSPATH . 'wp-admin/includes/plugin.php');
 
$plugin = 'sfwd-lms/sfwd_lms.php';

if (is_plugin_active($plugin)) {
    if (!is_admin()) {
        // include_once 'class-quizCategoryDisplay.php';
    }
    include_once 'class-filter-cat-questions.php';
}
