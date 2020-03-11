<?php
/**
 * Welcome Page
 *
 * Important upgrade notes
 *
 * @since 2.0.0
 *
 * @author Naomi C. Bush for gravity+ <support@gravityplus.pro>
 */
?>
<script type="text/javascript">

	function dismissMenu() {

		jQuery( '#gf_spinner' ).show();

		jQuery.post( ajaxurl, {
			             action: 'gf_dismiss_drip_menu'
		             },

		             function ( response ) {

			             document.location.href = '?page=gf_edit_forms';

			             jQuery( '#gf_spinner' ).hide();

		             }
		);

	}

	function getFieldGuide() {

		event.preventDefault();

		jQuery( this ).prop( 'disabled', true );

		jQuery( '#gf_field_guide_spinner' ).show();

		jQuery( '#get_field_guide_error_message' ).html( '' );

		var email = jQuery( '#drip_field_guide_email' ).val();

		if ( 0 !== email.length ) {

			jQuery.post( ajaxurl, {

				             email: email,
				             action: 'gf_get_drip_field_guide'
			             },

			             function ( response ) {

				             jQuery( '#gf_field_guide_spinner' ).hide();

				             if ( true == response.success ) {

					             jQuery( '#get_field_guide_form' ).html( "<span style='color:#019F01;'>The field guide is on it's way!</span>" );

				             }
				             else {

					             jQuery( '#get_field_guide_error_message' ).html( response.data['error_message'] );

					             jQuery( this ).prop( 'disabled', false );

				             }

			             }
			);

		}
		else {

			jQuery( '#gf_field_guide_spinner' ).hide();

			jQuery( '#get_field_guide_error_message' ).html( 'Please enter an email address' );

			jQuery( this ).prop( 'disabled', false );

		}

		return false;

	}
</script>
<style type="text/css">
	#get_field_guide_form .button-primary {

		background: #019F01;
		border: 0px;
		box-shadow: none;
		text-shadow: none;
		border-radius: 0;
	}

	#get_field_guide_form .button-primary:hover {
		background: #02be02;
	}

	.changelog h2 {
		margin: 25px 0;
	}

	.about-text img {

		float:left;
		margin-right:25px;
	}

	.about-text #description {
		position: relative;
		top:10px;
	}
</style>

<div class="wrap about-wrap">

	<h1><?php echo sprintf( __( 'Welcome to Drip Add-On %s', 'drip-gravity-forms' ), GFP_DRIP_CURRENT_VERSION ) ?></h1>

	<div class="about-text" style="background:white;padding:30px;">

		<img src="http://gravityplus.pro/gravity-forms-drip-field-guide-small.png" alt="Gravity Forms Drip Field Guide" />
		<span id="description"><?php _e( 'The Gravity Forms Drip Add-On is now', 'drip-gravity-forms' ); ?>
		<strong><?php _e( ' 10x more powerful.', 'drip-gravity-forms' ); ?></strong><br/><br/>
		<?php _e( 'There are some ', 'drip-gravity-forms' ); ?>
		<em><?php _e( 'things to look out for', 'drip-gravity-forms' ); ?></em><?php if ( '1' === $drip_field_guide ) {
			_e( ', so check your ', 'drip-gravity-forms' );
		} else {
			_e( ', so get the ', 'drip-gravity-forms' );
		} ?><span
			style="font-weight:bold;color:#019F01;"><?php _e( 'field guide', 'drip-gravity-forms' ); ?></span><?php _e( ', and save yourself some time and mistakes.', 'drip-gravity-forms' ); ?>
		<br/><br/>
		<?php if ( '1' !== $drip_field_guide ) { ?>
			<form method="post" id="get_field_guide_form">
				<input id="drip_field_guide_email" type="email" value="<?php echo $current_user->user_email; ?>"><input
					type="submit" class="button-primary"
					value="<?php _e( 'Send me the field guide', 'drip-gravity-forms' ); ?>"
					onclick="getFieldGuide();"><img id="gf_field_guide_spinner"
				                                    src="<?php echo GFCommon::get_base_url() . '/images/spinner.gif' ?>"
				                                    alt="<?php _e( 'Please wait...', 'drip-gravity-forms' ) ?>"
				                                    style="display:none;"/>
			</form>
			<br/> <span id="get_field_guide_error_message" style="color:red;"></span>
		<?php } ?>
			</span>
	</div>

	<div class="changelog">
		<h2><?php _e( 'A Brief Look', 'drip-gravity-forms' ) ?></h2>
		<hr/>
		<div class="feature-section col two-col">
			<div class="col-1">
				<h3><?php _e( 'Manage Drip Contextually', 'drip-gravity-forms' ) ?></h3>

				<p><?php _e( 'Drip Feeds are now accessed via the Drip sub-menu within the Form Settings for the form you would like to integrate Drip with.', 'drip-gravity-forms' ) ?></p>
			</div>
			<div class="col-2 last-feature">
				<img src="<?php echo GFP_DRIP_URL ?>images/new-drip-contextual-menu.png"
				     style="border:1px solid;max-width:75%;">
			</div>
		</div>

		<hr/>
		<div class="feature-section col two-col">
			<div class="col-1">
				<h3><?php _e( 'Choose Drip Action', 'drip-gravity-forms' ) ?></h3>

				<p><?php _e( 'Perform one or multiple Drip actions when a form is submitted.', 'drip-gravity-forms' ) ?></p>
			</div>
			<div class="col-2 last-feature">
				<img src="<?php echo GFP_DRIP_URL ?>images/new-drip-choose-action.png"
				     style="border:1px solid;max-width:75%;">
			</div>
		</div>

		<hr/>
		<div class="feature-section col two-col">
			<div class="col-1">
				<h3><?php _e( 'Map Custom Fields', 'drip-gravity-forms' ) ?></h3>

				<p><?php _e( 'You can now map *any* Gravity Forms field or metadata to your Drip custom fields.', 'drip-gravity-forms' ) ?></p>
			</div>
			<div class="col-2 last-feature">
				<img src="<?php echo GFP_DRIP_URL ?>images/new-drip-map-fields.png"
				     style="border:1px solid;max-width:75%;">
			</div>
		</div>

		<hr/>

		<form method="post" id="dismiss_menu_form" style="margin-top: 20px;">
			<input type="checkbox" name="dismiss_drip_menu" value="1" onclick="dismissMenu();">
			<label><?php _e( 'I understand this change, dismiss this message!', 'drip-gravity-forms' ) ?></label> <img
				id="gf_spinner" src="<?php echo GFCommon::get_base_url() . '/images/spinner.gif' ?>"
				alt="<?php _e( 'Please wait...', 'drip-gravity-forms' ) ?>" style="display:none;"/>
		</form>

	</div>
</div>