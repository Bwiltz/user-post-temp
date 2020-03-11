<?php
class GPPA_Compatibiliity_GravityFlow {

	private static $instance = null;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function __construct() {
		/* Source form is hydrated below. Target form is hydrated via "gform_form_pre_update_entry" in GPPA proper. */
		add_filter( 'gravityflowformconnector_update_entry_form', array( gp_populate_anything(), 'hydrate_form' ), 10, 2 );
	}

}

function gppa_compatibility_gravityflow() {
	return GPPA_Compatibiliity_GravityFlow::get_instance();
}
