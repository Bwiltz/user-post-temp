<?php

function payment_gateway_disable_country( $available_gateways ) {
	global $woocommerce;
	if ( is_admin() ) {
		return $available_gateways;
	}

	if ( isset( $available_gateways['stripe'] ) && $woocommerce->customer->get_billing_country() <> 'US' ) {
		unset( $available_gateways['stripe'] );
	} else if ( isset( $available_gateways['fastspring'] ) && $woocommerce->customer->get_billing_country() == 'US' ) {
		unset( $available_gateways['fastspring'] );
	} else if ( isset( $available_gateways['paypal_express'] ) && $woocommerce->customer->get_billing_country() <> 'US' ) {
		unset( $available_gateways['paypal_express'] );
}

	return $available_gateways;
}
 
add_filter( 'woocommerce_available_payment_gateways', 'payment_gateway_disable_country' );
