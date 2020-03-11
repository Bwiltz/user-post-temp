<?php
/*
 * @package   GFP_Drip\GFP_Drip_Addon
 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
 * @copyright 2016 gravity+
 * @license   GPL-2.0+
 * @since     2.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Class GFP_Drip_Addon
 *
 * Gravity Forms Add-On
 *
 * @since  2.0.0
 *
 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
 */
class GFP_Drip_Addon extends GFFeedAddOn {

	/**
	 * @var string Version number of the Add-On
	 */
	protected $_version;
	/**
	 * @var string Gravity Forms minimum version requirement
	 */
	protected $_min_gravityforms_version;
	/**
	 * @var string URL-friendly identifier used for form settings, add-on settings, text domain localization...
	 */
	protected $_slug;
	/**
	 * @var string Relative path to the plugin from the plugins folder
	 */
	protected $_path;
	/**
	 * @var string Full path to the plugin. Example: __FILE__
	 */
	protected $_full_path;
	/**
	 * @var string URL to the App website.
	 */
	protected $_url;
	/**
	 * @var string Title of the plugin to be used on the settings page, form settings and plugins page.
	 */
	protected $_title;
	/**
	 * @var string Short version of the plugin title to be used on menus and other places where a less verbose string is useful.
	 */
	protected $_short_title;
	/**
	 * @var array Members plugin integration. List of capabilities to add to roles.
	 */
	protected $_capabilities = array();

	// ------------ Permissions -----------
	/**
	 * @var string|array A string or an array of capabilities or roles that have access to the settings page
	 */
	protected $_capabilities_settings_page = array();

	/**
	 * @var string|array A string or an array of capabilities or roles that have access to the form settings
	 */
	protected $_capabilities_form_settings = array();
	/**
	 * @var string|array A string or an array of capabilities or roles that can uninstall the plugin
	 */
	protected $_capabilities_uninstall = array();

	/**
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @var GetDrip_WP_API | null
	 */
	protected $_drip_api = null;

	/**
	 * @see    GFAddOn::__construct
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param $args
	 */
	function __construct( $args ) {

		$this->_version                    = $args[ 'version' ];
		$this->_slug                       = $args[ 'plugin_slug' ];
		$this->_min_gravityforms_version   = $args[ 'min_gf_version' ];
		$this->_path                       = $args[ 'path' ];
		$this->_full_path                  = $args[ 'full_path' ];
		$this->_url                        = $args[ 'url' ];
		$this->_title                      = $args[ 'title' ];
		$this->_short_title                = $args[ 'short_title' ];
		$this->_capabilities               = $args[ 'capabilities' ];
		$this->_capabilities_settings_page = $args[ 'capabilities_settings_page' ];
		$this->_capabilities_form_settings = $args[ 'capabilities_form_settings' ];
		$this->_capabilities_uninstall     = $args[ 'capabilities_uninstall' ];

		$logger = new GFP_Drip_API_Logger();

		$api_token = trim( $this->get_plugin_setting( 'api_token' ) );

		$this->_drip_api = new GetDrip_WP_API( array(
			                                       'logger'    => $logger,
			                                       'api_token' => empty( $api_token ) ? null : $api_token
		                                       ) );

		parent::__construct();

	}

	/**
	 * @see    GFAddOn::upgrade
	 *
	 * Migrate old settings to new Add-On Framework settings and set welcome page redirect
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param $previous_version
	 */
	public function upgrade( $previous_version ) {

		if ( empty( $previous_version ) ) {

			$previous_version = get_option( 'gf_drip_version' );

		}

		$previous_is_pre_addon_framework = ! empty( $previous_version ) && version_compare( $previous_version, '2.0.0.dev1', '<' );

		if ( $previous_is_pre_addon_framework ) {

			//migrate plugin settings
			$this->migrate_settings();

			//migrate existing feeds to new table
			$this->migrate_feeds();

		}

		GFP_Drip::set_redirect();

	}

	/**
	 * @see    GFAddOn::init
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function init() {

		parent::init();

		$this->add_delayed_payment_support(
			array(
				'option_label' => __( 'Take Drip action only when a payment is received.', 'drip-gravity-forms' )
			)
		);
	}

	/**
	 * @see    GFAddOn::init_ajax
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function init_ajax() {

		parent::init_ajax();

		add_action( 'wp_ajax_gf_dismiss_drip_menu', array( $this, 'dismiss_drip_menu' ) );

		add_action( 'wp_ajax_gf_get_drip_field_guide', array( $this, 'get_drip_field_guide' ) );

	}

	/**
	 * @see    GFAddOn::init_admin
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function init_admin() {

		parent::init_admin();

		add_filter( 'gform_addon_navigation', array( $this, 'gform_addon_navigation' ) );

		$this->gfp_drip_redirect();

	}

	/**
	 * Add Drip welcome page to Forms menu, if user hasn't dismissed it
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param $menus
	 *
	 * @return array
	 */
	public function gform_addon_navigation( $menus ) {

		$current_user = wp_get_current_user();

		$dismiss_drip_menu = get_metadata( 'user', $current_user->ID, 'gfp_drip_dismiss_menu', true );

		if ( '1' !== $dismiss_drip_menu ) {

			$menus[ ] = array(
				'name'       => $this->_slug,
				'label'      => $this->get_short_title(),
				'callback'   => array( $this, 'temporary_plugin_page' ),
				'permission' => $this->_capabilities_form_settings
			);

		}

		return $menus;
	}

	/**
	 *  Redirect to settings page if not activating multiple plugins at once
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @uses   get_transient()
	 * @uses   delete_transient()
	 * @uses   admin_url()
	 * @uses   wp_redirect()
	 *
	 * @return void
	 */
	public static function gfp_drip_redirect() {

		if ( true == get_transient( 'gfp_drip_redirect' ) ) {

			delete_transient( 'gfp_drip_redirect' );

			if ( ! isset( $_GET[ 'activate-multi' ] ) ) {

				wp_redirect( self_admin_url( 'admin.php?page=gravityformsdrip' ) );

			}

		}
	}

	/**
	 * Send Drip field guide
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function get_drip_field_guide() {

		$email = rgpost( 'email' );

		if ( ! is_email( $email ) ) {

			wp_send_json_error( array( 'error_message' => __( 'Invalid email address', 'drip-gravity-forms' ) ) );

		}

		$api_url    = "https://gravityplus.pro/?gpp_action=updates_sign_up&email={$email}";
		$user_agent = 'GFP_Drip/' . GFP_DRIP_CURRENT_VERSION . '; ' . get_bloginfo( 'url' );
		$args       = array( 'user-agent' => $user_agent, 'body' => $email, 'sslverify' => false );

		$raw_response = wp_remote_post( $api_url, $args );

		if ( is_wp_error( $raw_response ) ) {

			$error_message = $raw_response->get_error_message( $raw_response->get_error_code() );

			wp_send_json_error( array( 'error_message' => $error_message ) );

		} else {

			$response = json_decode( wp_remote_retrieve_body( $raw_response ), true );

			if ( true == $response[ 'success' ] ) {

				update_metadata( 'user', get_current_user_id(), 'gfp_drip_field_guide', '1' );

				wp_send_json_success();

			} else {

				wp_send_json_error( array( 'error_message' => $response[ 'error' ] ) );

			}

		}

	}

	/**
	 * Don't show Drip welcome page for this user
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function dismiss_drip_menu() {

		$current_user = wp_get_current_user();

		update_metadata( 'user', $current_user->ID, 'gfp_drip_dismiss_menu', '1' );

	}

	/**
	 * Display Drip welcome page
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function temporary_plugin_page() {

		$current_user = wp_get_current_user();

		$drip_field_guide = get_metadata( 'user', $current_user->ID, 'gfp_drip_field_guide', true );

		include( GFP_DRIP_PATH . 'includes/views/plugin-page.php' );
	}

	/**
	 * @see    GFAddOn::plugin_settings_fields
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {

		$settings_fields = array();

		$settings_fields[ ] = array(
			'title'       => __( 'Authentication', 'drip-gravity-forms' ),
			'description' => '<p>' . sprintf( __( 'Enter your Drip API token. You can find it in your Drip account %shere%s (General Settings)', 'drip-gravity-forms' ), '<a href="https://www.getdrip.com/user/edit" target="_blank">', '</a>' ) . '</p>',
			'fields'      => array(
				array(
					'name'                => 'api_token',
					'label'               => __( 'API Token', 'drip-gravity-forms' ),
					'type'                => 'text',
					'validation_callback' => array( $this, 'validate_api_token' ),
					'feedback_callback'   => array( $this, 'check_api_token' ),
				),
				array(
					'type'     => 'save',
					'value'    => __( 'Authenticate', 'drip-gravity-forms' ),
					'messages' => array(
						'success' => __( 'API token authenticated', 'drip-gravity-forms' )
					)
				)
			)
		);

		return $settings_fields;
	}

	/**
	 * Validate API token
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param $field
	 * @param $field_setting
	 */
	public function validate_api_token( $field, $field_setting ) {

		if ( empty( $field_setting ) ) {

			$this->set_field_error( $field, __( 'An API token is required for this plugin to work', 'drip-gravity-forms' ) );

		} else {

			$valid = $this->_drip_api->validate_token( $field_setting );

			if ( ! $valid ) {

				$this->set_field_error( $field, __( 'Invalid API token', 'drip-gravity-forms' ) );

			}

		}

	}

	/**
	 * Check if current API token is valid or not
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param $api_token
	 * @param $field
	 *
	 * @return bool
	 */
	public function check_api_token( $api_token, $field ) {

		$status = false;

		if ( ! empty( $api_token ) ) {

			$status = $this->_drip_api->validate_token( $api_token );

		}

		return $status;
	}

	/**
	 * @see    GFFeedAddOn::can_create_feed
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @return bool
	 */
	public function can_create_feed() {

		$api_token = $this->get_plugin_setting( 'api_token' );

		return ! empty( $api_token );
	}

	/**
	 * @see    GFFeedAddOn::feed_list_message
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @return bool | string
	 */
	public function feed_list_message() {

		$message = parent::feed_list_message();

		if ( $message !== false ) {

			return $message;

		}

		$api_token = $this->get_plugin_setting( 'api_token' );

		if ( empty( $api_token ) ) {

			$settings_label = __( 'Authenticate your Drip Account', 'drip-gravity-forms' );
			$settings_link  = sprintf( '<a href="%s">%s</a>', $this->get_plugin_settings_url(), $settings_label );

			return sprintf( __( 'To get started, please %s.', 'drip-gravity-forms' ), $settings_link );
		}

		return false;
	}

	/**
	 * @see    GFFeedAddOn::can_duplicate_feed
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param array|int $id
	 *
	 * @return bool
	 */
	public function can_duplicate_feed( $id ) {

		return true;

	}

	/**
	 * @see    GFFeedAddOn::feed_list_columns
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @return array
	 */
	public function feed_list_columns() {

		return array(
			'feedName'    => __( 'Name', 'drip-gravity-forms' ),
			'drip_action' => __( 'Drip Action', 'drip-gravity-forms' )
		);

	}

	/**
	 * Get value to display for the Drip action column, in the feed list
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function get_column_value_drip_action( $item ) {

		$column_value = '';

		$drip_action_choices = $this->get_drip_action_choices();

		foreach ( $drip_action_choices as $action_choice ) {

			if ( $action_choice[ 'value' ] == $item[ 'meta' ][ 'drip_action' ] ) {

				$column_value = $action_choice[ 'label' ];

				break;
			}

		}

		return $column_value;
	}

	/**
	 * @see    GFFeedAddOn::feed_settings_fields
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @return array
	 */
	public function feed_settings_fields() {

		$feed_field_name = array(
			'label'    => __( 'Name', 'drip-gravity-forms' ),
			'type'     => 'text',
			'name'     => 'feedName',
			'tooltip'  => __( 'Name for this feed', 'drip-gravity-forms' ),
			'class'    => 'medium',
			'required' => true
		);

		$feed_field_drip_action = array(
			'label'    => __( 'Drip Action', 'drip-gravity-forms' ),
			'type'     => 'select',
			'name'     => 'drip_action',
			'tooltip'  => __( 'Select the Drip action you want to take when someone submits this form', 'drip-gravity-forms' ),
			'choices'  => $this->get_drip_action_choices(),
			'onchange' => "jQuery(this).parents('form').submit();jQuery( this ).parents( 'form' ).find(':input').prop('disabled', true );",
			'required' => true
		);

		$feed_field_account_id = array(
			'label'    => __( 'Drip Account', 'drip-gravity-forms' ),
			'type'     => 'select',
			'name'     => 'account_id',
			'tooltip'  => __( 'Select the Drip account you want to use', 'drip-gravity-forms' ),
			'choices'  => $this->get_drip_account_choices(),
			'onchange' => "jQuery(this).parents('form').submit();jQuery( this ).parents( 'form' ).find(':input').prop('disabled', true );",
			'required' => true
		);

		$feed_field_subscriber_email = array(
			'label'    => __( 'Subscriber Email', 'drip-gravity-forms' ),
			'type'     => 'field_select',
			'name'     => 'email',
			'args'     => array( 'input_types' => array( 'email', 'hidden' ) ),
			'required' => true
		);

		$feed_field_new_email = array(
			'label'   => __( 'New Email Address', 'drip-gravity-forms' ),
			'type'    => 'field_select',
			'name'    => 'new_email',
			'tooltip' => __( 'If updating, a new email address for the subscriber', 'drip-gravity-forms' ),
			'args'    => array( 'input_types' => array( 'email', 'hidden' ) )
		);

		$feed_field_custom_fields = array(
			'label'          => __( 'Custom Fields', 'drip-gravity-forms' ),
			'type'           => 'dynamic_field_map',
			'name'           => 'custom_fields',
			'tooltip'        => __( 'Enter your custom field name, then select the form field with the value for that field', 'drip-gravity-forms' ),
			'disable_custom' => false
		);

		$feed_field_add_tags = array(
			'label'       => __( 'Tags to add', 'drip-gravity-forms' ),
			'type'        => 'text',
			'name'        => 'add_tags',
			'tooltip'     => __( 'A comma-separated list of tags to add to this subscriber', 'drip-gravity-forms' ),
			'class'       => 'medium',
			'placeholder' => 'Customer,SEO'
		);

		$feed_field_remove_tags = array(
			'label'       => __( 'Tags to remove', 'drip-gravity-forms' ),
			'type'        => 'text',
			'name'        => 'remove_tags',
			'tooltip'     => __( 'A comma-separated list of tags to remove from this subscriber', 'drip-gravity-forms' ),
			'class'       => 'medium',
			'placeholder' => 'Customer,SEO'
		);

		$feed_field_user_id = array(
			'label'   => __( 'User ID', 'drip-gravity-forms' ),
			'type'    => 'field_select',
			'name'    => 'user_id',
			'tooltip' => __( 'A unique identifier for the user in your database, such as a primary key', 'drip-gravity-forms' ),
		);

		$feed_field_prospect = array(
			'label'   => __( 'Attach lead score?', 'drip-gravity-forms' ),
			'type'    => 'checkbox',
			'name'    => 'prospect_checkbox',
			'tooltip' => __( 'Should Drip attach a lead score to the subscriber (when lead scoring is enabled)?', 'drip-gravity-forms' ),
			'choices' => array(
				array(
					'name'          => 'prospect',
					'label'         => '',
					'default_value' => 1,
				)
			),
		);

		$feed_field_base_lead_score = array(
			'label'   => __( 'Start lead score at', 'drip-gravity-forms' ),
			'type'    => 'select_custom',
			'name'    => 'base_lead_score',
			'tooltip' => __( 'The starting value for lead score calculation for this subscriber', 'drip-gravity-forms' ),
			'choices' => array(
				array_merge( array(
					             'value' => '',
					             'label' => __( 'Select a Field', 'drip-gravity-forms' )
				             ), $this->get_form_fields_as_choices( $this->get_current_form(), array(
					                                                                            'input_types' => array(
						                                                                            'number',
						                                                                            'hidden'
					                                                                            )
				                                                                            )
				) )
			)
		);

		$feed_field_starting_email_index = array(
			'label'   => __( 'Email to send first', 'drip-gravity-forms' ),
			'type'    => 'select_custom',
			'name'    => 'starting_email_index',
			'tooltip' => __( 'The index of the email to send first. Starts at 0', 'drip-gravity-forms' ),
			'choices' => array(
				array_merge( array(
					             'value' => '',
					             'label' => __( 'Select a Field', 'drip-gravity-forms' )
				             ), $this->get_form_fields_as_choices( $this->get_current_form(), array(
					'input_types' => array(
						'number',
						'hidden'
					)
				) ) )
			)
		);

		$feed_field_custom_event_action = array(
			'label'    => __( 'Action', 'drip-gravity-forms' ),
			'type'     => 'select_custom',
			'name'     => 'action',
			'tooltip'  => __( 'The name of the action taken, e.g. "Logged in"', 'drip-gravity-forms' ),
			'required' => true,
			'choices'  => array(
				array_merge( array(
					             'value' => '',
					             'label' => __( 'Select a Field', 'drip-gravity-forms' )
				             ), $this->get_form_fields_as_choices( $this->get_current_form() ) )
			)
		);

		$feed_field_custom_event_properties = array(
			'label'          => __( 'Custom Event Properties', 'drip-gravity-forms' ),
			'type'           => 'dynamic_field_map',
			'name'           => 'properties',
			'tooltip'        => __( 'Enter your custom event property name, then select the form field with the value for that property', 'drip-gravity-forms' ),
			'disable_custom' => false
		);

		$feed_field_campaigns = array(
			'label'    => __( 'Drip Campaign', 'drip-gravity-forms' ),
			'type'     => 'select',
			'name'     => 'campaign_id',
			'choices'  => $this->get_drip_campaign_choices( $this->get_setting( 'account_id' ), $this->get_setting( 'drip_action' ) ),
			'required' => true
		);

		$feed_field_conditional_logic = array(
			'name'    => 'conditionalLogic',
			'label'   => __( 'Conditional Logic', 'drip-gravity-forms' ),
			'type'    => 'feed_condition',
			'tooltip' => '<h6>' . __( 'Conditional Logic', 'drip-gravity-forms' ) . '</h6>' . __( 'When conditions are enabled, form submissions will only be sent to Drip when the conditions are met. When disabled, all form submissions will be sent to Drip.', 'drip-gravity-forms' )
		);

		$sections = array(
			array(
				'title'  => __( 'Feed Name', 'drip-gravity-forms' ),
				'fields' => array(
					$feed_field_name
				)
			),
			array(
				'title'  => __( 'Drip Action', 'drip-gravity-forms' ),
				'fields' => array(
					$feed_field_account_id,
					$feed_field_drip_action
				)
			),
			array(
				'title'      => __( 'Drip Fields', 'drip-gravity-forms' ),
				'dependency' => array(
					'field'  => 'drip_action',
					'values' => array( 'create_or_update' )
				),
				'fields'     => array(
					$feed_field_subscriber_email,
					$feed_field_new_email,
					$feed_field_custom_fields
				)
			),
			array(
				'title'      => __( 'Drip Fields', 'drip-gravity-forms' ),
				'dependency' => array(
					'field'  => 'drip_action',
					'values' => array( 'record_event' )
				),
				'fields'     => array(
					$feed_field_subscriber_email,
					$feed_field_custom_event_action,
					$feed_field_custom_event_properties
				)
			),
			array(
				'title'      => __( 'Campaign', 'drip-gravity-forms' ),
				'dependency' => array(
					'field'  => 'drip_action',
					'values' => array( 'campaign_subscribe', 'campaign_unsubscribe' )
				),
				'fields'     => array(
					$feed_field_campaigns
				)
			),
			array(
				'title'      => __( 'Drip Fields', 'drip-gravity-forms' ),
				'dependency' => array(
					'field'  => 'drip_action',
					'values' => array( 'campaign_unsubscribe' )
				),
				'fields'     => array(
					$feed_field_subscriber_email
				)
			),
			array(
				'title'      => __( 'Drip Fields', 'drip-gravity-forms' ),
				'dependency' => array(
					'field'  => 'drip_action',
					'values' => array( 'campaign_subscribe' )
				),
				'fields'     => array(
					$feed_field_subscriber_email,
					$feed_field_custom_fields
				)
			),
			array(
				'title'      => __( 'Other Options', 'drip-gravity-forms' ),
				'dependency' => array(
					'field'  => 'drip_action',
					'values' => array( 'create_or_update' )
				),
				'fields'     => array(
					$feed_field_user_id,
					$feed_field_add_tags,
					$feed_field_remove_tags,
					$feed_field_prospect,
					$feed_field_base_lead_score
				)
			),
			array(
				'title'      => __( 'Other Options', 'drip-gravity-forms' ),
				'dependency' => array(
					'field'  => 'drip_action',
					'values' => array( 'record_event' )
				),
				'fields'     => array(
					$feed_field_prospect
				)
			),
			array(
				'title'      => __( 'Other Options', 'drip-gravity-forms' ),
				'dependency' => array(
					'field'  => 'drip_action',
					'values' => array( 'campaign_subscribe' )
				),
				'fields'     => array(
					$feed_field_user_id,
					$feed_field_starting_email_index,
					$feed_field_add_tags,
					$feed_field_prospect,
					$feed_field_base_lead_score
				)
			),
			array(
				'title'  => __( 'Conditional Logic', 'drip-gravity-forms' ),
				'fields' => array(
					$feed_field_conditional_logic
				)
			)
		);

		return $sections;
	}

	/**
	 * Get Drip actions to display in settings_select field
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @return array
	 */
	private function get_drip_action_choices() {

		$this->log_debug( __METHOD__ );

		return array(
			array(
				'label' => '',
				'value' => ''
			),
			array(
				'label' => __( 'Create or Update Subscriber', 'drip-gravity-forms' ),
				'value' => 'create_or_update'
			),
			array(
				'label' => __( 'Record Event', 'drip-gravity-forms' ),
				'value' => 'record_event'
			),
			array(
				'label' => __( 'Subscribe to Campaign', 'drip-gravity-forms' ),
				'value' => 'campaign_subscribe'
			),
			array(
				'label' => __( 'Unsubscribe from Campaign', 'drip-gravity-forms' ),
				'value' => 'campaign_unsubscribe'
			)
		);

	}

	/**
	 * Get Drip accounts to display in settings_select field
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @return array
	 */
	private function get_drip_account_choices() {

		$this->log_debug( __METHOD__ );

		$account_choices = array(
			array(
				'label' => '',
				'value' => ''
			)
		);

		$this->_drip_api->set_api_token( $this->get_plugin_setting( 'api_token' ) );

		$accounts = $this->_drip_api->list_accounts();

		if ( ! empty( $accounts ) ) {

			foreach ( $accounts[ 'accounts' ] as $account ) {

				$account_choices[ ] = array( 'label' => $account[ 'name' ], 'value' => $account[ 'id' ] );

			}

		}

		return $account_choices;
	}

	/**
	 * Get campaigns for selected Drip account, to display in settings_select field
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param $account_id
	 * @param $drip_action
	 *
	 * @return array
	 */
	private function get_drip_campaign_choices( $account_id, $drip_action ) {

		$this->log_debug( __METHOD__ );

		$account_campaigns = array(
			array(
				'label' => '',
				'value' => ''
			)
		);

		if ( ! empty( $account_id ) && ! empty( $drip_action ) ) {

			$this->_drip_api->set_api_token( $this->get_plugin_setting( 'api_token' ) );

			$campaigns = $this->_drip_api->list_campaigns( $account_id );

			if ( ! empty( $campaigns ) ) {

				if ( 1 < $campaigns[ 'meta' ][ 'total_pages' ] ) {

					$all_campaigns = $campaigns[ 'campaigns' ];

					while ( $campaigns[ 'meta' ][ 'page' ] < $campaigns[ 'meta' ][ 'total_pages' ] ) {

						$campaigns = $this->_drip_api->list_campaigns( $account_id, $campaigns[ 'meta' ][ 'page' ] + 1 );

						if ( ! empty( $campaigns ) ) {

							$all_campaigns = array_merge( $all_campaigns, $campaigns[ 'campaigns' ] );

						}

					}

				} else {

					$all_campaigns = $campaigns[ 'campaigns' ];

				}

				foreach ( $all_campaigns as $campaign ) {

					$account_campaigns[ ] = array( 'label' => $campaign[ 'name' ], 'value' => $campaign[ 'id' ] );

				}

			}

			if ( 'campaign_unsubscribe' == $drip_action ) {

				$account_campaigns[ ] = array(
					'label' => __( 'All Campaigns', 'drip-gravity-forms' ),
					'value' => 'all'
				);

			}

		}

		return $account_campaigns;

	}

	/**
	 * @see    GFFeedAddOn::process_feed
	 *
	 * Performs the Drip action when the form is submitted
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param $feed
	 * @param $entry
	 * @param $form
	 */
	public function process_feed( $feed, $entry, $form ) {

		$this->_drip_api->set_api_token( $this->get_plugin_setting( 'api_token' ) );

		$account_id = (string) $this->get_setting( 'account_id', '', $feed[ 'meta' ] );

		$drip_action = (string) $this->get_setting( 'drip_action', '', $feed[ 'meta' ] );

		switch ( $drip_action ) {

			case 'create_or_update':

				$api_params = $this->get_create_or_update_api_params( $feed, $entry, $form );

				$this->_drip_api->create_or_update_subscriber( $account_id, $api_params );

				break;

			case 'record_event':

				$api_params = $this->get_record_event_api_params( $feed, $entry, $form );

				$this->_drip_api->record_event( $account_id, $api_params );

				break;

			case 'campaign_subscribe':

				$api_params = $this->get_campaign_subscribe_api_params( $feed, $entry, $form );

				$campaign_id = (string) $this->get_setting( 'campaign_id', '', $feed[ 'meta' ] );

				$this->_drip_api->subscribe_to_campaign( $account_id, $campaign_id, $api_params );

				break;

			case 'campaign_unsubscribe':

				$campaign_id = (string) $this->get_setting( 'campaign_id', '', $feed[ 'meta' ] );

				$subscriber_id = (string) $this->get_mapped_field_value( 'email', $form, $entry, $feed[ 'meta' ] );

				$this->_drip_api->unsubscribe_from_campaign( $account_id, $subscriber_id, $campaign_id );

				break;

		}

	}

	/**
	 * Get field values from entry, for a dynamic field map
	 *
	 * TODO Note: this doesn't work for image or signature fields
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param $field_name
	 * @param $feed
	 * @param $entry
	 * @param $form
	 *
	 * @return array
	 */
	private function get_dynamic_field_map_values( $field_name, $feed, $entry, $form ) {

		$field_map_values = array();


		$field_map_field_ids = $this->get_dynamic_field_map_fields( $feed, $field_name );


		foreach ( $field_map_field_ids as $name => $field_id ) {
			if (!empty($name)) {
				$field_map_values[ $name ] = $this->get_field_value( $form, $entry, $field_id );
			}
		}


		return $field_map_values;

	}

	/**
	 * Get values from entry and format for Drip API call
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param $feed
	 * @param $entry
	 * @param $form
	 *
	 * @return array
	 */
	private function get_create_or_update_api_params( $feed, $entry, $form ) {

		$api_params[ 'email' ] = (string) $this->get_mapped_field_value( 'email', $form, $entry, $feed[ 'meta' ] );

		$api_params[ 'ip_address' ] = $entry[ 'ip' ];

		$new_email = (string) $this->get_mapped_field_value( 'new_email', $form, $entry, $feed[ 'meta' ] );

		if ( ! empty( $new_email ) ) {

			$api_params[ 'new_email' ] = $new_email;

		}

		$user_id = (string) $this->get_mapped_field_value( 'user_id', $form, $entry, $feed[ 'meta' ] );

		if ( ! empty( $user_id ) ) {

			$api_params[ 'user_id' ] = $user_id;

		}

		$custom_fields = $this->get_dynamic_field_map_values( 'custom_fields', $feed, $entry, $form );

		if ( ! empty( $custom_fields ) ) {

			$api_params[ 'custom_fields' ] = $custom_fields;

		}

		$tags = explode( ',', $this->get_setting( 'add_tags', '', $feed[ 'meta' ] ) );

		if ( is_array( $tags ) && ! empty( $tags[ 0 ] ) ) {

			$api_params[ 'tags' ] = $tags;

		}

		$remove_tags = explode( ',', $this->get_setting( 'remove_tags', '', $feed[ 'meta' ] ) );

		if ( is_array( $remove_tags ) && ! empty( $remove_tags[ 0 ] ) ) {

			$api_params[ 'remove_tags' ] = $remove_tags;

		}

		$prospect = ( '1' == $this->get_setting( 'prospect', '', $feed[ 'meta' ] ) ) ? true : false;

		if ( ! empty( $prospect ) ) {

			$api_params[ 'prospect' ] = $prospect;

		}

		$base_lead_score = ( 'gf_custom' == $this->get_setting( 'base_lead_score', '', $feed[ 'meta' ] ) ) ? $this->get_setting( 'base_lead_score_custom', '', $feed[ 'meta' ] ) : (int) $this->get_mapped_field_value( 'base_lead_score', $form, $entry, $feed[ 'meta' ] );

		if ( ! empty( $base_lead_score ) ) {

			$api_params[ 'base_lead_score' ] = $base_lead_score;

		}

		return array( 'subscribers' => array( $api_params ) );

	}

	/**
	 * Get values from entry and format for Drip API call
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param $feed
	 * @param $entry
	 * @param $form
	 *
	 * @return array
	 */
	private function get_record_event_api_params( $feed, $entry, $form ) {

		$api_params[ 'email' ] = (string) $this->get_mapped_field_value( 'email', $form, $entry, $feed[ 'meta' ] );

		$api_params[ 'action' ] = ( 'gf_custom' == $this->get_setting( 'action', '', $feed[ 'meta' ] ) ) ? $this->get_setting( 'action_custom', '', $feed[ 'meta' ] ) : (string) $this->get_mapped_field_value( 'action', $form, $entry, $feed[ 'meta' ] );

		$event_properties = $this->get_dynamic_field_map_values( 'properties', $feed, $entry, $form );

		if ( ! empty( $event_properties ) ) {

			$api_params[ 'properties' ] = $event_properties;

		}

		$prospect = ( '1' == $this->get_setting( 'prospect', '', $feed[ 'meta' ] ) ) ? true : false;

		if ( ! empty( $prospect ) ) {

			$api_params[ 'prospect' ] = $prospect;

		}


		return array( 'events' => array( $api_params ) );

	}

	/**
	 *Get values from entry and format for Drip API call
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @param $feed
	 * @param $entry
	 * @param $form
	 *
	 * @return array
	 */
	private function get_campaign_subscribe_api_params( $feed, $entry, $form ) {

		$api_params[ 'email' ] = (string) $this->get_mapped_field_value( 'email', $form, $entry, $feed[ 'meta' ] );

		$user_id = (string) $this->get_mapped_field_value( 'user_id', $form, $entry, $feed[ 'meta' ] );

		if ( ! empty( $user_id ) ) {

			$api_params[ 'user_id' ] = $user_id;

		}

		$starting_email_index = ( 'gf_custom' == $this->get_setting( 'starting_email_index', '', $feed[ 'meta' ] ) ) ? $this->get_setting( 'starting_email_index_custom', '', $feed[ 'meta' ] ) : (int) $this->get_mapped_field_value( 'starting_email_index', $form, $entry, $feed[ 'meta' ] );

		if ( ! empty( $starting_email_index ) ) {

			$api_params[ 'starting_email_index' ] = $starting_email_index;

		}

		$custom_fields = $this->get_dynamic_field_map_values( 'custom_fields', $feed, $entry, $form );

		if ( ! empty( $custom_fields ) ) {

			$api_params[ 'custom_fields' ] = $custom_fields;

		}

		$tags = explode( ',', $this->get_setting( 'add_tags', '', $feed[ 'meta' ] ) );

		if ( is_array( $tags ) && ! empty( $tags[ 0 ] ) ) {

			$api_params[ 'tags' ] = $tags;

		}

		$prospect = ( '1' == $this->get_setting( 'prospect', '', $feed[ 'meta' ] ) ) ? true : false;

		if ( ! empty( $prospect ) ) {

			$api_params[ 'prospect' ] = $prospect;

		}

		$base_lead_score = ( 'gf_custom' == $this->get_setting( 'base_lead_score', '', $feed[ 'meta' ] ) ) ? $this->get_setting( 'base_lead_score_custom', '', $feed[ 'meta' ] ) : (int) $this->get_mapped_field_value( 'base_lead_score', $form, $entry, $feed[ 'meta' ] );

		if ( ! empty( $base_lead_score ) ) {

			$api_params[ 'base_lead_score' ] = $base_lead_score;

		}

		return array( 'subscribers' => array( $api_params ) );
	}

	/***********************************************************************************************
	 * ------------------------------BACKWARDS COMPATIBILITY-----------------------------------------
	 * //**********************************************************************************************/

	/**
	 * Migrate old plugin settings to new plugin settings format
	 *
	 * TODO: Note that we are leaving the old settings in place until the next upgrade, so the user can rollback in case something goes wrong
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function migrate_settings() {

		//copy plugin settings
		$old_settings = get_option( 'gf_drip_settings' );

		$new_settings = array( 'api_token' => $old_settings[ 'apikey' ] );

		$this->update_plugin_settings( $new_settings );

	}

	/**
	 * Migrate old feeds to new feed format
	 *
	 * TODO: Note that we are leaving the old feeds table in place until the next upgrade, so the user can rollback in case something goes wrong
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 */
	public function migrate_feeds() {

		//get old feeds
		$old_feeds = $this->get_old_feeds();

		if ( $old_feeds ) {

			$counter = 1;

			foreach ( $old_feeds as $old_feed ) {

				$form_id = $old_feed[ 'form_id' ];

				$custom_fields = array_filter( (array) rgars( $old_feed, 'meta/field_map' ) );

				foreach ( $custom_fields as $form_field_input_id => $custom_field_name ) {

					if ( 'email' == $custom_field_name ) {

						$email = str_replace( "input_{$form_id}_", '', $form_field_input_id );

						break;

					}

				}

				if ( ! empty( $email ) ) {

					$feed_name = 'Create/Update Feed ' . $counter;
					$is_active = rgar( $old_feed, 'is_active' ) ? '1' : '0';

					$new_meta = array(
						'feedName'    => $feed_name,
						'account_id'  => rgars( $old_feed, 'meta/account_id' ),
						'drip_action' => 'create_or_update',
						'email'       => $email
					);

					//custom fields
					$new_custom_fields = array();

					foreach ( $custom_fields as $form_field_input_id => $custom_field_name ) {

						if ( ( $form_field_input_id && $custom_field_name ) && ( 'email' != $custom_field_name ) ) {

							$new_custom_fields[ ] = array(
								'key'        => 'gf_custom',
								'value'      => str_replace( "input_{$form_id}_", '', $form_field_input_id ),
								'custom_key' => $custom_field_name
							);

						}

					}

					if ( ! empty( $new_custom_fields ) ) {

						$new_meta[ 'custom_fields' ] = $new_custom_fields;

					}

					//add conditional logic, legacy only allowed one condition
					$conditional_enabled = rgars( $old_feed, 'meta/optin_enabled' );

					if ( $conditional_enabled ) {

						$new_meta[ 'feed_condition_conditional_logic' ] = 1;

						$new_meta[ 'feed_condition_conditional_logic_object' ] = array(
							'conditionalLogic' =>
								array(
									'actionType' => 'show',
									'logicType'  => 'all',
									'rules'      => array(
										array(
											'fieldId'  => rgar( $old_feed[ 'meta' ], 'optin_field_id' ),
											'operator' => rgar( $old_feed[ 'meta' ], 'optin_operator' ),
											'value'    => rgar( $old_feed[ 'meta' ], 'optin_value' )
										),
									)
								)
						);
					} else {

						$new_meta[ 'feed_condition_conditional_logic' ] = 0;

					}

					$this->insert_feed( $form_id, $is_active, $new_meta );
					$counter ++;

					//if, event enabled, create event feed

					if ( rgars( $old_feed, 'meta/send_an_event_enabled' ) ) {

						$feed_name = 'Event Feed ' . $counter;

						$new_event_meta = array(
							'feedName'               => $feed_name,
							'account_id'             => $new_meta[ 'account_id' ],
							'drip_action'            => 'record_event',
							'email'                  => $new_meta[ 'email' ],
							'action'                 => 'gf_custom',
							'action_custom'          => rgars( $old_feed, 'meta/drip_send_event_name' ),
							'feed_conditional_logic' => $new_meta[ 'feed_condition_conditional_logic' ]
						);

						if ( $new_meta[ 'feed_condition_conditional_logic' ] ) {

							$new_event_meta[ 'feed_conditional_logic_object' ] = $new_meta[ 'feed_condition_conditional_logic_object' ];

						}

						$this->insert_feed( $form_id, $is_active, $new_event_meta );
						$counter ++;

					}

				}

			}

		}

	}

	/**
	 * Get feeds from old addon table
	 *
	 * Thanks to Gravity Forms for the code
	 *
	 * @since  2.0.0
	 *
	 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
	 *
	 * @return array|bool|null|object
	 */
	public function get_old_feeds() {

		global $wpdb;

		$table_name = $wpdb->prefix . 'rg_drip';

		if ( ! $this->table_exists( $table_name ) ) {

			return false;

		}

		$form_table_name = GFFormsModel::get_form_table_name();

		$sql = "SELECT s.id, s.is_active, s.form_id, s.meta, f.title as form_title
						FROM {$table_name} s
						INNER JOIN {$form_table_name} f ON s.form_id = f.id";

		$this->log_debug( __METHOD__ . "(): getting old feeds: {$sql}" );

		$results = $wpdb->get_results( $sql, ARRAY_A );

		$this->log_debug( __METHOD__ . "(): error?: {$wpdb->last_error}" );

		$count = sizeof( $results );

		$this->log_debug( __METHOD__ . "(): count: {$count}" );

		for ( $i = 0; $i < $count; $i ++ ) {
			$results[ $i ][ 'meta' ] = maybe_unserialize( $results[ $i ][ 'meta' ] );
		}

		return $results;

	}

	//------------------------------END BACKWARDS COMPATIBILITY-----------------------------------------

}
