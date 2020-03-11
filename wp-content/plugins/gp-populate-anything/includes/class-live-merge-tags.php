<?php

/**
 * Class GP_Populate_Anything_Live_Merge_Tags
 */
class GP_Populate_Anything_Live_Merge_Tags {

	private static $instance = null;

	private $live_attrs_on_page = array();
	private $_escapes = array();

	private $live_merge_tag_regex_option_placeholder = '/(<option.*?class=\'gf_placeholder\'>)(.*?@({.*?:?.+?}).*?)<\/option>/';
	private $live_merge_tag_regex_option_choice = '/(<option.*>)(.*?@({.*?:?.+?}).*?)<\/option>/';
	private $live_merge_tag_regex_textarea = '/(<textarea.*>)(.*?@({.*?:?.+?}).*?)<\/textarea>/';
	private $live_merge_tag_regex = '/@({((.*?):?(.+?))})/';
	private $live_merge_tag_regex_attr = '/([a-zA-Z-]+)=([\'"]([^\'"]*@{.*?:?.+?}[^\'"]*)(?<!\\\)[\'"])/';
	private $script_regex = '/<script[\s\S]*?<\/script>/';

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function __construct() {
		add_filter( 'gform_field_choice_markup_pre_render', array( $this, 'replace_live_merge_tag_select_field_option' ), 10, 4 );

		add_filter( 'gform_field_content', array( $this, 'replace_live_merge_tag_select_placeholder' ), 99, 2 );
		add_filter( 'gform_field_content', array( $this, 'replace_live_merge_tag_textarea_default_value' ), 99, 2 );
		add_filter( 'gform_field_content', array( $this, 'add_live_value_attr' ), 99, 2 );
		add_filter( 'gform_field_content', array( $this, 'add_select_default_value_attr' ), 99, 2 );

		add_filter( 'gform_get_form_filter', array( $this, 'preserve_scripts' ), 98, 2 );
		add_filter( 'gform_get_form_filter', array( $this, 'preserve_product_field_label' ), 98, 2 );
		add_filter( 'gform_get_form_filter', array( $this, 'replace_live_merge_tag_attr' ), 99, 2 );
		add_filter( 'gform_get_form_filter', array( $this, 'replace_live_merge_tag_non_attr' ), 99, 2 );
		add_filter( 'gform_get_form_filter', array( $this, 'unescape_live_merge_tags' ), 99, 2 );
		add_filter( 'gform_get_form_filter', array( $this, 'add_localization_attr_variable' ), 99, 2 );
		add_filter( 'gform_get_form_filter', array( $this, 'restore_escapes' ), 100, 2 );

		add_filter( 'gform_replace_merge_tags', array( $this, 'replace_live_merge_tags_static' ), 10, 7 );
		add_filter( 'gform_admin_pre_render',   array( $this, 'replace_field_label_live_merge_tags_static' ) );

		add_action( 'wp_ajax_gppa_get_live_merge_tag_values', array( $this, 'ajax_get_live_merge_tag_values' ) );
		add_action( 'wp_ajax_nopriv_gppa_get_live_merge_tag_values', array( $this, 'ajax_get_live_merge_tag_values' ) );

		/**
		 * Prevent replacement of Live Merge Tags in Preview Submission.
		 */
		add_filter( 'gpps_pre_replace_merge_tags', array( $this, 'escape_live_merge_tags' ) );
		add_filter( 'gpps_post_replace_merge_tags', array( $this, 'unescape_live_merge_tags' ) );

	}

	/**
	 * Gravity Forms outputs scripts in the form markup for things like conditional logic. Sometimes field settings
	 * such as the default value are included. Without intervention, the regular expressions in this class will match
	 * the Live Merge tags inside the JavaScript thus wreaking havoc and causing JavaScript errors.
	 *
	 * The easiest workaround is to shelve the JavaScript, run our replacements, and then re-add the JavaScript.
	 *
	 * @param $form_string
	 * @param $form
	 *
	 * @return string
	 */
	public function preserve_scripts( $form_string, $form ) {

		preg_match_all( $this->script_regex, $form_string, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $form_string;
		}

		foreach ( $matches as $index => $match ) {
			$placeholder = "%%SCRIPT_FORM_{$form['id']}_{$index}%%";

			$this->_escapes[ $placeholder ] = $match[0];
			$form_string                    = str_replace( $match[0], $placeholder, $form_string );
		}

		return $form_string;

	}

	/**
	 * Gravity Forms validates Product fields using hashing and if the product name doesn't match due to a LMT on
	 * the Product field's label, it will fail validation.
	 *
	 * We need to escape the LMT on the hidden input that contains the product name.
	 *
	 * See ticket #13740
	 *
	 * @param $form_string
	 * @param $form
	 *
	 * @return string
	 */
	public function preserve_product_field_label( $form_string, $form ) {

		preg_match_all( '/ginput_container_singleproduct\'>[.\s]*?<input type=\'hidden\' name=\'input_\d+\.\d+\' value=\'(.*)?\' class=\'gform_hidden\' \/>/', $form_string, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $form_string;
		}

		foreach ( $matches as $index => $match ) {
			$placeholder = "%%PRODUCT_NAME_{$form['id']}_{$index}%%";

			/**
			 * $search and $replace are needed since we're only replacing $match[0] inside of $match[1]
			 *
			 * Without this, it can get a bit aggressive and replace the LMT in other locations.
			 */
			$search = $match[0];
			$replace = str_replace( $match[1], $placeholder, $search );

			$this->_escapes[ $placeholder ] = $match[1];
			$form_string                    = str_replace( $search, $replace, $form_string );
		}

		return $form_string;

	}

	public function restore_escapes( $form_string, $form ) {

		foreach ( $this->_escapes as $placeholder => $script ) {
			$form_string = str_replace( $placeholder, $script, $form_string );
		}

		return $form_string;

	}

	public function ajax_get_live_merge_tag_values() {

		check_ajax_referer( 'gppa', 'security' );

		$form = GFAPI::get_form( $_REQUEST['form-id'] );

		$merge_tag_results = array();
		$fake_lead         = array();
		$field_values      = gp_populate_anything()->get_field_values_from_request();

		/**
		 * Map the field values to $_POST to ensure that $field->get_value_save_entry() works as expected.
		 */
		foreach ( $field_values as $input => $value ) {
			$_POST[ 'input_' . $input ] = $value;
		}

		foreach ( $field_values as $input => $value ) {
			$field = GFFormsModel::get_field( $form, $input );

			if ( ! $field ) {
				continue;
			}

			if ( $field->has_calculation() || $field->type == 'total' ) {
				$fake_lead[ $input ] = $value;
			} else {
				$fake_lead[ $input ] = $field->get_value_save_entry( $value, $form, $input, null, null );
			}

		}

		/**
		 * Flush GF cache to prevent issues from the fake lead creation from before.
		 *
		 * For posterity, issues encountered in the past are issues with conditional logic.
		 */
		GFCache::flush();

		foreach ( rgar( $_REQUEST, 'merge-tags', array() ) as $live_merge_tag ) {
			$live_merge_tag = stripslashes( $live_merge_tag );

			/* Strip @ from live merge tags */
			$to_be_replaced = preg_replace( $this->live_merge_tag_regex, '$1', $live_merge_tag );

			$merge_tag_results[ $live_merge_tag ] = $this->get_live_merge_tag_value( $to_be_replaced, $form, $fake_lead );
		}

		wp_send_json( $merge_tag_results );

	}

	public function replace_live_merge_tag_attr( $form_string, $form ) {

		preg_match_all( $this->live_merge_tag_regex_attr, $form_string, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $form_string;
		}

		foreach ( $matches as $match ) {
			$full_match = $match[0];
			$merge_tag  = $match[3];

			$output = $this->get_live_merge_tag_value( $merge_tag, $form );

			$replaced_attr = $match[1] . '="' . esc_attr( $output ) . '"';

			if ( strpos( $match[1], 'data-gppa-live-merge-tag' ) === 0 ) {
				continue;
			}

			$data_attr_name  = 'data-gppa-live-merge-tag-' . $match[1];
			$data_attr_value = $this->escape_live_merge_tags( $match[3] );
			$data_attr       = $data_attr_name . '="' . esc_attr( $data_attr_value ) . '"';

			if ( ! isset( $this->live_attrs_on_page[ $form['id'] ] ) ) {
				$this->live_attrs_on_page[ $form['id'] ] = array();
			}

			$this->live_attrs_on_page[ $form['id'] ][] = 'data-gppa-live-merge-tag-' . $match[1];

			$form_string = str_replace( $full_match, $replaced_attr . ' ' . $data_attr, $form_string );
		}

		return $form_string;

	}

	public function replace_live_merge_tag_select_placeholder( $content, $field ) {

		preg_match_all( $this->live_merge_tag_regex_option_placeholder, $content, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $content;
		}

		$form = GFAPI::get_form( $field->formId );

		/**
		 * $match[0] = Entire <option>...</option> string
		 * $match[1] = Starting tag and attributes
		 * $match[2] = Inner HTML of option
		 * $match[3] = First live merge tag that's seen
		 */
		foreach ( $matches as $match ) {

			$full_match = $match[0];

			$output = $this->get_live_merge_tag_value( $match[2], $form );
			$data_attr = 'data-gppa-live-merge-tag-innerHtml="' . esc_attr( $this->escape_live_merge_tags( $match[2] ) ) . '"';

			$class_string = "class='gf_placeholder'";

			$full_match_replacement = str_replace( $match[2], $output, $full_match );
			$full_match_replacement = str_replace( $class_string, $class_string . ' ' . $data_attr, $full_match_replacement );

			if ( ! isset( $this->live_attrs_on_page[ $form['id'] ] ) ) {
				$this->live_attrs_on_page[ $form['id'] ] = array();
			}

			$this->live_attrs_on_page[ $form['id'] ][] = 'data-gppa-live-merge-tag-innerHtml';

			$content = str_replace( $full_match, $full_match_replacement, $content );
		}

		return $content;

	}

	public function replace_live_merge_tag_textarea_default_value( $content, $field ) {

		preg_match_all( $this->live_merge_tag_regex_textarea, $content, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $content;
		}

		$form = GFAPI::get_form( $field->formId );

		/**
		 * $match[0] = Entire <textarea>...</textarea> string
		 * $match[1] = Starting tag and attributes
		 * $match[2] = Inner HTML of textarea
		 * $match[3] = First live merge tag that's seen
		 */
		foreach ( $matches as $match ) {

			$full_match = $match[0];

			$output = $this->get_live_merge_tag_value( $match[2], $form );
			$data_attr = 'data-gppa-live-merge-tag-innerHtml="' . esc_attr( $this->escape_live_merge_tags( $match[2] ) ) . '"';

			$full_match_replacement = str_replace( $match[2], $output, $full_match );
			$full_match_replacement = str_replace( '<textarea ', '<textarea ' . $data_attr . ' ', $full_match_replacement );

			if ( ! isset( $this->live_attrs_on_page[ $form['id'] ] ) ) {
				$this->live_attrs_on_page[ $form['id'] ] = array();
			}

			$this->live_attrs_on_page[ $form['id'] ][] = 'data-gppa-live-merge-tag-innerHtml';

			$content = str_replace( $full_match, $full_match_replacement, $content );
		}

		return $content;

	}

	/**
	 * In some cases such as using a multi-page form, Gravity Forms will supply GPPA with form values which will overwrite
	 * the values that were initially LMTs. Because of this, LMTs won't be detected by the broad form filters that
	 * add in the data attr's for the LMTs.
	 *
	 * To get around this, we check if there are LMTs in the value and if not we re-add the data attr as long as there
	 * are LMTs in the field's default value.
	 *
	 * Caveat: This won't work with textareas for now.
	 *
	 * @param $content
	 * @param $field
	 *
	 * @return mixed
	 */
	public function add_live_value_attr( $content, $field ) {

		preg_match_all( '/value=([\'"]([^\'"]*@{.*?:?.+?}[^\'"]*)(?<!\\\)[\'"])/', $content, $matches, PREG_SET_ORDER );

		/**
		 * If there are already LMTs in the value, then bail out since the filters for the entry form string will
		 * add in the data attrs.
		 */
		if ( $matches && count( $matches ) ) {
			return $content;
		}

		if ( ! preg_match( '/@{.*?:?.+?}/', $field->defaultValue ) ) {
			return $content;
		}

		$data_attr = 'data-gppa-live-merge-tag-value="' . esc_attr( $this->escape_live_merge_tags( $field->defaultValue ) ) . '"';

		return str_replace( ' value=\'', ' ' . $data_attr . ' value=\'', $content );

	}

	/**
	 * @param $content
	 * @param $field
	 *
	 * @return mixed
	 */
	public function add_select_default_value_attr( $content, $field ) {

		preg_match_all( '/<select name=\'input_(\d+(\.\d+)?)\'/', $content, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $content;
		}

		$default_values = ! empty( $field->inputs ) ? wp_list_pluck( $field->inputs, 'defaultValue', 'id' ) : array();
		$default_values[ $field->id ] = $field->defaultValue;

		$has_lmt = false;

		foreach ( array_values( $default_values ) as $default_value ) {
			if ( preg_match( '/@{.*?:?.+?}/', $default_value ) ) {
				$has_lmt = true;
				break;
			}
		}

		if ( !$has_lmt ) {
			return $content;
		}

		if ( ! isset( $this->live_attrs_on_page[ $field->formId ] ) ) {
			$this->live_attrs_on_page[ $field->formId ] = array();
		}

		foreach ( $matches as $match ) {
			$input_id = $match[1];
			$default_value = $default_values[ $input_id ];

			/**
			 * With future AJAX optimizations, we will need to output get_live_merge_tag_value for initial load.
			 */
			$data_attr = 'data-gppa-live-merge-tag-innerHtml="' . esc_attr( $this->escape_live_merge_tags( $default_value ) ) . '"';

			$this->live_attrs_on_page[ $field->formId ][] = 'data-gppa-live-merge-tag-innerHtml';

			$content = str_replace( $match[0], $match[0] . ' ' . $data_attr, $content );
		}

		return $content;

	}

	public function replace_live_merge_tag_select_field_option( $choice_markup, $choice, $field, $value ) {

		preg_match_all( $this->live_merge_tag_regex_option_choice, $choice_markup, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $choice_markup;
		}

		$form = GFAPI::get_form( $field->formId );

		/**
		 * $match[0] = Entire <option>...</option> string
		 * $match[1] = Starting tag and attributes
		 * $match[2] = Option label
		 * $match[3] = First live merge tag that's seen
		 */
		foreach ( $matches as $match ) {

			$full_match = $match[0];

			$output = $this->get_live_merge_tag_value( $match[2], $form );
			$data_attr = 'data-gppa-live-merge-tag-innerHtml="' . esc_attr( $this->escape_live_merge_tags( $match[2] ) ) . '"';

			$full_match_replacement = str_replace( $match[2], $output, $full_match );
			$full_match_replacement = str_replace( '<option ', '<option ' . $data_attr . ' ', $full_match_replacement );

			if ( ! isset( $this->live_attrs_on_page[ $form['id'] ] ) ) {
				$this->live_attrs_on_page[ $form['id'] ] = array();
			}

			$this->live_attrs_on_page[ $form['id'] ][] = 'data-gppa-live-merge-tag-innerHtml';

			$choice_markup = str_replace( $full_match, $full_match_replacement, $choice_markup );

		}

		return $choice_markup;

	}

	public function replace_live_merge_tag_non_attr( $form_string, $form ) {

		preg_match_all( $this->live_merge_tag_regex, $form_string, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $form_string;
		}

		foreach ( $matches as $match ) {
			$full_match = $match[0];
			$merge_tag  = $match[1];

			$populated_merge_tag = $this->get_live_merge_tag_value( $merge_tag, $form );

			$span        = '<span data-gppa-live-merge-tag="' . esc_attr( $this->escape_live_merge_tags( $full_match ) ) . '">' . $populated_merge_tag . '</span>';
			$form_string = str_replace( $full_match, $span, $form_string );
		}

		return $form_string;

	}

	/**
	 * Escape live merge tags to prevent regex interference.
	 *
	 * @param $string
	 */
	public function escape_live_merge_tags( $string ) {
		return preg_replace( $this->live_merge_tag_regex, '#!GPPA!!$2!!GPPA!#', $string );
	}

	public function unescape_live_merge_tags( $form_string ) {
		return preg_replace( '/#!GPPA!!((.*?):?(.+?))!!GPPA!#/', '@{$1}', $form_string );
	}

	public function add_localization_attr_variable( $form_string, $form ) {
		if ( ! empty ( $this->live_attrs_on_page[ $form['id'] ] ) ) {
			wp_localize_script( 'gp-populate-anything', "GPPA_LIVE_ATTRS_FORM_{$form['id']}", array_values( array_unique( $this->live_attrs_on_page[ $form['id'] ] ) ) );
		}

		return $form_string;
	}

	public function extract_merge_tag_modifiers( $non_live_merge_tag ) {

		$merge_tag_parts = explode( ':', $non_live_merge_tag );

		if ( count( $merge_tag_parts ) < 3 ) {
			return array();
		}

		$modifiers     = array();
		$modifiers_str = rtrim( $merge_tag_parts[2], '}' );

		preg_match_all( '/([a-z]+)(?:(?:\[(.+?)\])|,?)/', $modifiers_str, $matches, PREG_SET_ORDER );

		foreach ( $matches as $match_group ) {
			$modifiers[ $match_group[1] ] = isset( $match_group[2] ) ? $match_group[2] : true;
		}

		return $modifiers;

	}

	public function get_live_merge_tag_value( $merge_tag, $form, $entry_values = null ) {

		if ( ! $entry_values ) {
			$entry_values = gp_populate_anything()->get_posted_field_values( $form );
		}

		$merge_tag           = preg_replace( $this->live_merge_tag_regex, '$1', $merge_tag );

		$merge_tag_value     = GFCommon::replace_variables( $merge_tag, $form, $entry_values );
		$merge_tag_modifiers = $this->extract_merge_tag_modifiers( $merge_tag );

		while ( preg_match_all( $this->live_merge_tag_regex, $merge_tag_value, $populated_merge_tag_matches, PREG_SET_ORDER ) ) {
			$merge_tag_value = $this->get_live_merge_tag_value( $merge_tag_value, $form, $entry_values );
		}

		if ( ( $fallback = rgar( $merge_tag_modifiers, 'fallback' ) ) && ! $merge_tag_value ) {
			return $fallback;
		}

		return $merge_tag_value;

	}

	/**
	 * In some cases, live merge tags should be replaced statically without the need to make them "live" (i.e. in field
	 * labels when rendering the {all_fields} merge tag).
	 *
	 * @return string $text
	 */
	public function replace_live_merge_tags_static( $text, $form, $entry, $url_encode = false, $esc_html = false, $nl2br = false, $format = 'html' ) {

		if ( ! $entry ) {
			return $text;
		}

		preg_match_all( $this->live_merge_tag_regex, $text, $matches, PREG_SET_ORDER );

		if ( ! $matches ) {
			return $text;
		}

		foreach ( $matches as $match ) {
			$full_match = $match[0];
			$merge_tag  = $match[1];

			/**
			 * Prevent recursion.
			 */
			remove_filter( 'gform_replace_merge_tags', array( $this, 'replace_live_merge_tags_static' ), 10 );
			$populated_merge_tag = GFCommon::replace_variables( $merge_tag, $form, $entry );
			add_filter( 'gform_replace_merge_tags', array( $this, 'replace_live_merge_tags_static' ), 10, 7 );

			$text = str_replace( $full_match, $populated_merge_tag, $text );
		}

		return $text;

	}

	public function replace_field_label_live_merge_tags_static( $form ) {

		$entry = false;
		if( in_array( GFForms::get_page(), array( 'entry_detail', 'entry_detail_edit' ) ) ) {
			$entry = GFAPI::get_entry( rgget( 'lid' ) );
		}

		if( ! $entry || is_wp_error( $entry ) ) {
			return $form;
		}

		foreach( $form['fields'] as $field ) {
			$field->label = $this->replace_live_merge_tags_static( $field->label, $form, $entry );
		}

		return $form;
	}

}
