<?php
namespace wdmcheckanswerexam;

if (!class_exists('FilterCatQuestions')) {
    class FilterCatQuestions
    {
        public function __construct()
        {
            add_action('wp_enqueue_scripts', array( $this, 'enqueueScripts'));
        }

        public static function init()
        {
            static $instance = false;

            if (!$instance) {
                $instance = new FilterCatQuestions();
            }

            return $instance;
        }

        /**
         * Function to fetch Attributes in ShortCode Present in  Post Content
         * @return Array  Array Of Attributes
         * @author Shubham
         */
        public function checkQuizShortcode()
        {
            global $post;
            $pattern = get_shortcode_regex();
            
            $result =array();
            
            if (preg_match_all('/'. $pattern .'/s', $post->post_content, $matches)) {
                $keys = array();
                $result = array();
                foreach ($matches[0] as $key => $value) {
                    // $matches[3] return the shortcode attribute as string
                    // replace space with '&' for parse_str() function
                    $get = str_replace(" ", "&", $matches[3][$key]);
                    parse_str($get, $output);
                    
                    //get all shortcode attribute keys
                    $keys = array_unique(array_merge($keys, array_keys($output)));
                    $result[] = $output;
                }
                //var_dump($result);
                if ($keys && $result) {
                    // Loop the result array and add the missing shortcode attribute key
                    foreach ($result as $key => $value) {
                        // Loop the shortcode attribute key
                        foreach ($keys as $attr_key) {
                            $result[$key][$attr_key] = isset($result[$key][$attr_key]) ?
                            $result[$key][$attr_key] : null;
                        }
                        //sort the array key
                        ksort($result[$key]);
                    }
                }
    
                //display the result
                foreach ($result as $key => $value) {
                    if ($value['quiz_id']) {
                        $page_id = $value['quiz_id'];
                    }
                    # code...
                }
                      
                $page_id = str_replace("\"", "", $page_id);
                return (int)$page_id;
            }
        }

        public function enqueueScripts()
        {
            global $post;
            global $wpdb;
            
            $page_id = get_the_ID();

            if (!empty($page_id)) {
                $all_questions  = array();
                $avail_que      = array();
                
                $has_shortcode =  has_shortcode(get_post($page_id)->post_content, 'ld_quiz');

                if ($has_shortcode) {
                    $page_id = $this->checkQuizShortcode();
                }
               
                $page_type = get_post_type($page_id);
                
                if ('sfwd-quiz'== $page_type) {
                    $quiz_label = \LearnDash_Custom_Label::get_label('quiz');
                    if (get_post_meta($page_id, 'wdm_check_answer', true)) {
                        wp_dequeue_script(
                            'wpProQuiz_front_javascript'
                        );
               
                        wp_register_script(
                            'wpProQuiz_front_javascript',
                            plugins_url('assets/wpProQuiz_front_javascript_check_answer.js', __FILE__),
                            array( 'jquery' ),
                            filemtime(__DIR__ . '/assets/wpProQuiz_front_javascript_check_answer.js'),
                            true
                        );

                        wp_localize_script(
                            'wpProQuiz_front_javascript',
                            'wdm_sfwd_quiz_check_time',
                            array(
                            'ajaxurl' => admin_url('admin-ajax.php'),
                            )
                        );
 
                        wp_enqueue_script('wpProQuiz_front_javascript');
                
                        wp_enqueue_script(
                            'wpProQuiz_front_javascript',
                            plugins_url('assets/wpProQuiz_front_javascript_check_answer.js', __FILE__),
                            array( 'jquery' ),
                            filemtime(__DIR__ . '/assets/wpProQuiz_front_javascript_check_answer.js'),
                            true
                        );
                    }
                }
            }
        }
    }
    FilterCatQuestions::init();
}
