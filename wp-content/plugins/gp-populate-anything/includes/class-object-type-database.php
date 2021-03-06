<?php

class GPPA_Object_Type_Database extends GPPA_Object_Type {

	protected $_restricted = true;

	private static $blacklisted_columns = array( 'password', 'user_pass', 'user_activation_key' );

	public function __construct($id) {
		parent::__construct($id);

		add_action( 'gppa_pre_object_type_query_database', array( $this, 'add_filter_hooks' ) );
	}

	public function add_filter_hooks() {
		add_filter('gppa_object_type_database_filter', array( $this, 'process_filter_default'), 10, 4 );
	}

	public function get_label() {
		return esc_html__( 'Database: ', 'gp-populate-anything' ) . DB_NAME;
	}

	public function get_groups() {
		return array(
			'columns' => array(
				'label' => esc_html__( 'Columns', 'gp-populate-anything' ),
			),
		);
	}

	public function get_primary_property() {
		return array(
			'id'       => 'table',
			'label'    => esc_html__( 'Table', 'gp-populate-anything' ),
			'callable' => array( $this, 'get_tables' ),
		);
	}

	public function get_properties( $table = null ) {

		if ( !$table ) {
			return array();
		}

		$properties = array();

		foreach ( $this->get_columns( $table ) as $column ) {
			$properties[ $column['value'] ] = array(
				'group'    => 'columns',
				'label'    => $column['label'],
				'value'    => $column['value'],
				'orderby'  => true,
				'callable' => array( $this, 'get_column_values' ),
				'args'     => array( $table, $column['value'] ),
			);
		}

		return $properties;

	}

	public function get_db() {
		global $wpdb;

		return $wpdb;
	}

	public function get_tables() {
		$result = $this->get_db()->get_results( 'SHOW TABLES', ARRAY_N );

		return wp_list_pluck( $result, 0 );
	}

	public function get_columns( $table ) {
		$table   = self::esc_sql_ident( $table );
		$columns = array();

		$results = $this->get_db()->get_results( "SHOW COLUMNS FROM $table", ARRAY_N );

		foreach ( $results as $column ) {
			$columns[] = array(
				'value'   => $column[0],
				'label'   => $column[0],
			);
		}

		return $columns;
	}

	public function get_column_values( $table, $col ) {
		$table = self::esc_sql_ident( $table );
		$col   = self::esc_sql_ident( $col );

		$query = apply_filters( 'gppa_object_type_database_column_value_query', "SELECT DISTINCT $col FROM $table", $table, $col, $this );
		$result = $this->get_db()->get_results( $query, ARRAY_N );

		return $this->filter_values( wp_list_pluck( $result, 0 ) );
	}

	public function process_filter_default( $query_builder_args, $args ) {

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

		$query_builder_args['where'][ $filter_group_index ][] = $this->build_where_clause( $primary_property_value, $property_id, $filter['operator'], $filter_value );

		return $query_builder_args;

	}

	public function default_query_args( $args ) {

		/**
		 * @var $primary_property_value string
		 * @var $field_values array
		 * @var $templates array
		 * @var $filter_groups array
		 * @var $ordering array
		 * @var $field array
		 * @var $unique boolean
		 */
		extract( $args );

		$orderby = rgar( $ordering, 'orderby' );
		$order   = rgar( $ordering, 'order', 'ASC' );

		return array(
			'select'   => "*",
			'from'     => $primary_property_value,
			'where'    => array(),
			'order_by' => $orderby,
			'order'    => $order,
		);

	}

	public function query( $args ) {

		global $wpdb;

		$query_args = $this->process_filter_groups( $args, $this->default_query_args( $args ) );

		$query = $this->build_mysql_query( apply_filters( 'gppa_object_type_database_pre_query_parts', $query_args, $this ) );

		return $wpdb->get_results( apply_filters( 'gppa_object_type_database_query', $query, $args, $this ) );

	}

	public function get_object_prop_value( $object, $prop ) {

		if ( in_array( $prop, self::$blacklisted_columns ) ) {
			return null;
		}

		if ( ! isset ( $object->{$prop} ) ) {
			return null;
		}

		return $object->{$prop};

	}

}
