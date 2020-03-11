<?php

class GPPA_Object_Type_GF_Entry extends GPPA_Object_Type {

	private static $excluded_fields = array(
		'list',
	);

	public function __construct($id) {
		parent::__construct($id);

		add_action( 'gppa_pre_object_type_query_gf_entry', array( $this, 'add_filter_hooks' ) );

		add_filter( 'gppa_process_template', array( $this, 'maybe_combine_multi_input_entry_template'), 5, 7 );
	}

	public function add_filter_hooks() {
		add_filter('gppa_object_type_gf_entry_filter', array( $this, 'process_filter_default'), 10, 4 );
	}

	public function get_label() {
		return esc_html__( 'Gravity Forms Entry', 'gp-populate-anything' );
	}

	public function get_groups() {
		return array(
			'fields' => array(
				'label' => esc_html__( 'Fields', 'gp-populate-anything' ),
			),
		);
	}

	public function get_primary_property() {
		return array(
			'id'       => 'form',
			'label'    => esc_html__( 'Form', 'gp-populate-anything' ),
			'callable' => array( $this, 'get_forms' ),
		);
	}

	public function get_properties( $form_id = null ) {

		if ( !$form_id ) {
			return array();
		}

		$properties = array(
			'id' => array(
				'label'    => 'Entry ID',
				'value'    => 'id',
				'callable' => array( $this, 'get_col_rows' ),
				'args'     => array( GFFormsModel::get_entry_table_name(), 'id' ),
				'orderby'  => true,
			),
			'created_by' => array(
				'label'    => 'Created by (User ID)',
				'value'    => 'created_by',
				'callable' => '__return_empty_array',
				'orderby'  => true,
			)
		);

		foreach ( $this->get_form_fields( $form_id ) as $form_field ) {
			$properties[ 'gf_field_' . $form_field['value'] ] = array(
				'value'    => $form_field['value'],
				'group'    => 'fields',
				'label'    => $form_field['label'],
				'callable' => array( $this, 'get_form_fields_values' ),
				'args'     => array( $form_id, $form_field['value'] ),
				'orderby'  => true,
			);
		}

		return $properties;

	}

	public function get_object_prop_value( $object, $prop ) {

		$prop = preg_replace( '/^gf_field_/', '', $prop );

		if ( ! isset ( $object->{$prop} ) ) {
			return null;
		}

		return $object->{$prop};

	}

	public function process_filter_default( $gf_query_where, $args ) {

		/**
		 * @var $filter_value
		 * @var $filter
		 * @var $filter_group
		 * @var $filter_group_index
		 * @var $primary_property_value
		 * @var $property
		 * @var $property_id
		 */
		extract($args);

		if ( ! isset( $gf_query_where[ $filter_group_index ] ) ) {
			$gf_query_where[ $filter_group_index ] = array();
		}

		switch ( strtoupper( $filter['operator'] ) ) {
			case 'CONTAINS' :
				$operator     = GF_Query_Condition::LIKE;
				$filter_value = $this->get_sql_value( $filter['operator'], $filter_value );
				break;
			case 'STARTS_WITH' :
				$operator     = GF_Query_Condition::LIKE;
				$filter_value = $this->get_sql_value( $filter['operator'], $filter_value );
				break;
			case 'ENDS_WITH' :
				$operator     = GF_Query_Condition::LIKE;
				$filter_value = $this->get_sql_value( $filter['operator'], $filter_value );
				break;
			case 'IS NOT' :
			case 'ISNOT' :
			case '<>' :
				$operator = GF_Query_Condition::NEQ;
				break;
			case 'LIKE' :
				$operator = GF_Query_Condition::LIKE;
				break;
			case 'NOT IN' :
				$operator = GF_Query_Condition::NIN;
				break;
			case 'IN' :
				$operator = GF_Query_Condition::IN;
				break;
			case '>=':
				$operator = GF_Query_Condition::GTE;
				break;
			case '<=':
				$operator = GF_Query_Condition::LTE;
				break;
			case '<':
				$operator = GF_Query_Condition::LT;
				break;
			case '>':
				$operator = GF_Query_Condition::GT;
				break;
			case 'IS' :
			case '=' :
			default:
				$operator = GF_Query_Condition::EQ;
				break;
		}

		if ( is_numeric( $filter_value ) ) {
			$filter_value = floatval( $filter_value );
		}

		$gf_query_where[ $filter_group_index ][] = new GF_Query_Condition(
			new GF_Query_Column( rgar( $property, 'value' ), (int) $primary_property_value ),
			$operator,
			new GF_Query_Literal( $filter_value )
		);

		return $gf_query_where;

	}

	public function exclude_trashed_entries ( $where_filter_groups ) {

		$where_not_trashed = new GF_Query_Condition(
			new GF_Query_Column( 'status' ),
			GF_Query_Condition::NEQ,
			new GF_Query_Literal( 'trash' )
		);

		return call_user_func_array( array( 'GF_Query_Condition', '_and' ), array( $where_filter_groups, $where_not_trashed ) );

	}

	public function query( $args ) {

		/**
		 * @var $primary_property_value string
		 * @var $field_values array
		 * @var $filter_groups array
		 * @var $ordering array
		 * @var $field array
		 */
		extract( $args );

		if ( !$primary_property_value ) {
			return array();
		}

		$gf_query = new GF_Query( $primary_property_value, null, array(
			'direction' => rgar( $ordering, 'order', 'ASC' ),
			'key' => str_replace('gf_field_', '', rgar( $ordering, 'orderby' ) ),
		), array(
			'page_size' => apply_filters( 'gppa_query_limit', 501, $this ),
		) );

		$gf_query_where_groups = $this->process_filter_groups( $args, array() );

		foreach ( $gf_query_where_groups as $gf_query_where_index => $gf_query_where_group ) {
			$gf_query_where_groups[$gf_query_where_index] = call_user_func_array( array( 'GF_Query_Condition', '_and' ), $gf_query_where_group );
		}

		$where_filter_groups = call_user_func_array( array( 'GF_Query_Condition', '_or' ), $gf_query_where_groups );
		$where = $this->exclude_trashed_entries( $where_filter_groups );

		$gf_query->where( $where );

		$entries = $gf_query->get();

		foreach ( $entries as $entry_index => $entry ) {
			$entry_object = new StdClass();

			foreach ( $entry as $key => $value ) {
				$entry_object->{$key} = $value;
			}

			$entries[ $entry_index ] = $entry_object;
		}

		return $entries;

	}

	public function get_forms() {

		$forms = GFFormsModel::get_forms();

		return wp_list_pluck( $forms, 'title', 'id' );

	}

	public function get_form_fields( $form_id ) {

		$form = GFAPI::get_form( $form_id );

		if ( ! $form || ! $form_id ) {
			return array();
		}

		$output = array();

		foreach ( $form['fields'] as $field ) {
			if (array_search($field['type'], self::$excluded_fields) !== false) {
				continue;
			}

			if ( empty( $field['inputs'] ) || in_array( $field['type'], GP_Populate_Anything::get_interpreted_multi_input_field_types() ) ) {
				$output[] = array(
					'value' => $field['id'],
					'label' => $field['label'],
				);
			} else if ( is_array( $field['inputs'] ) ) {
				$output[] = array(
					'value' => $field['id'],
					'label' => $field['label'],
				);

				foreach ( $field['inputs'] as $input ) {
					$output[] = array(
						'value' => $input['id'],
						'label' => $field['label'] . ' (' . $input['label'] . ')',
					);
				}
			}
		}

		return $output;

	}

	public function get_form_fields_values( $form_id, $input_id ) {

		global $wpdb;

		$entry_meta_table = GFFormsModel::get_entry_meta_table_name();

		$sql = "SELECT meta_value from $entry_meta_table WHERE form_id = %d AND meta_key = %s";

		return $wpdb->get_col( $wpdb->prepare( $sql, $form_id, $input_id ) );

	}

	/**
	 * Combine multi-input fields such as checkboxes into a single array. Useful for populating multi-selectable choice
	 * inputs with the value of a multi-selectable choice entry field.
	 *
	 * @param $template_value
	 * @param $field
	 * @param $template
	 * @param $populate
	 * @param $object
	 * @param $object_type
	 * @param $objects
	 *
	 * @return mixed
	 */
	public function maybe_combine_multi_input_entry_template( $template_value, $field, $template, $populate, $object, $object_type, $objects ) {

		if ( $object_type->id !== $this->id ) {
			return $template_value;
		}

		if ( ! $object || is_scalar( $object ) ) {
			return $template_value;
		}

		$templates = rgar( $field, 'gppa-' . $populate . '-templates', array() );
		$template  = rgar( $templates, $template );

		if ( strpos( $template, 'gf_field_' ) !== 0 ) {
			return $template_value;
		}

		$field_id = str_replace( 'gf_field_', '', $template );

		/**
		 * We do not want to loop the object below unless the field ID is an integer and nothing is found in the entry
		 * with the supplied field ID.
		 *
		 * Coerce field ID string to an integer using "+ 0". This trick works well with is_float as well.
		 */
		if ( (isset( $object->{$field_id} ) && is_scalar( $object->{$field_id} )) || ! is_int( $field_id + 0 ) ) {
			return $template_value;
		}

		$output = array();

		foreach ( $object as $key => $value ) {

			if ( absint( $key ) === absint( $field_id ) ) {
				$output[ $key ] = $value;
			}

		}

		$output = array_unique( array_filter( $output ) );

		return json_encode( $output );

	}

	public function does_field_accept_json( $field ) {
		return ! empty( $field->inputs ) || $field->storageType === 'json';
	}

}
