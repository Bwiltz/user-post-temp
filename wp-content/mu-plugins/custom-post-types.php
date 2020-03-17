<?php

/**
 * Registers the `submission` post type.
 */
function submission_init() {
	register_post_type( 'submission', array(
		'labels'                => array(
			'name'                  => __( 'submissions', 'YOUR-TEXTDOMAIN' ),
			'singular_name'         => __( 'submission', 'YOUR-TEXTDOMAIN' ),
			'all_items'             => __( 'All submissions', 'YOUR-TEXTDOMAIN' ),
			'archives'              => __( 'submission Archives', 'YOUR-TEXTDOMAIN' ),
			'attributes'            => __( 'submission Attributes', 'YOUR-TEXTDOMAIN' ),
			'insert_into_item'      => __( 'Insert into submission', 'YOUR-TEXTDOMAIN' ),
			'uploaded_to_this_item' => __( 'Uploaded to this submission', 'YOUR-TEXTDOMAIN' ),
			'featured_image'        => _x( 'Featured Image', 'submission', 'YOUR-TEXTDOMAIN' ),
			'set_featured_image'    => _x( 'Set featured image', 'submission', 'YOUR-TEXTDOMAIN' ),
			'remove_featured_image' => _x( 'Remove featured image', 'submission', 'YOUR-TEXTDOMAIN' ),
			'use_featured_image'    => _x( 'Use as featured image', 'submission', 'YOUR-TEXTDOMAIN' ),
			'filter_items_list'     => __( 'Filter submissions list', 'YOUR-TEXTDOMAIN' ),
			'items_list_navigation' => __( 'submissions list navigation', 'YOUR-TEXTDOMAIN' ),
			'items_list'            => __( 'submissions list', 'YOUR-TEXTDOMAIN' ),
			'new_item'              => __( 'New submission', 'YOUR-TEXTDOMAIN' ),
			'add_new'               => __( 'Add New', 'YOUR-TEXTDOMAIN' ),
			'add_new_item'          => __( 'Add New submission', 'YOUR-TEXTDOMAIN' ),
			'edit_item'             => __( 'Edit submission', 'YOUR-TEXTDOMAIN' ),
			'view_item'             => __( 'View submission', 'YOUR-TEXTDOMAIN' ),
			'view_items'            => __( 'View submissions', 'YOUR-TEXTDOMAIN' ),
			'search_items'          => __( 'Search submissions', 'YOUR-TEXTDOMAIN' ),
			'not_found'             => __( 'No submissions found', 'YOUR-TEXTDOMAIN' ),
			'not_found_in_trash'    => __( 'No submissions found in trash', 'YOUR-TEXTDOMAIN' ),
			'parent_item_colon'     => __( 'Parent submission:', 'YOUR-TEXTDOMAIN' ),
			'menu_name'             => __( 'submissions', 'YOUR-TEXTDOMAIN' ),
		),
		'public'                => true,
		'hierarchical'          => false,
		'show_ui'               => true,
		'show_in_nav_menus'     => true,
		'supports'              => array( 'title', 'editor' ),
		'has_archive'           => true,
		'rewrite'               => true,
		'query_var'             => true,
		'menu_position'         => null,
		'menu_icon'             => 'dashicons-admin-post',
		'show_in_rest'          => true,
		'rest_base'             => 'submission',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	) );

}
add_action( 'init', 'submission_init' );

/**
 * Sets the post updated messages for the `submission` post type.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `submission` post type.
 */
function submission_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['submission'] = array(
		0  => '', // Unused. Messages start at index 1.
		/* translators: %s: post permalink */
		1  => sprintf( __( 'submission updated. <a target="_blank" href="%s">View submission</a>', 'YOUR-TEXTDOMAIN' ), esc_url( $permalink ) ),
		2  => __( 'Custom field updated.', 'YOUR-TEXTDOMAIN' ),
		3  => __( 'Custom field deleted.', 'YOUR-TEXTDOMAIN' ),
		4  => __( 'submission updated.', 'YOUR-TEXTDOMAIN' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'submission restored to revision from %s', 'YOUR-TEXTDOMAIN' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		/* translators: %s: post permalink */
		6  => sprintf( __( 'submission published. <a href="%s">View submission</a>', 'YOUR-TEXTDOMAIN' ), esc_url( $permalink ) ),
		7  => __( 'submission saved.', 'YOUR-TEXTDOMAIN' ),
		/* translators: %s: post permalink */
		8  => sprintf( __( 'submission submitted. <a target="_blank" href="%s">Preview submission</a>', 'YOUR-TEXTDOMAIN' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		/* translators: 1: Publish box date format, see https://secure.php.net/date 2: Post permalink */
		9  => sprintf( __( 'submission scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview submission</a>', 'YOUR-TEXTDOMAIN' ),
		date_i18n( __( 'M j, Y @ G:i', 'YOUR-TEXTDOMAIN' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		/* translators: %s: post permalink */
		10 => sprintf( __( 'submission draft updated. <a target="_blank" href="%s">Preview submission</a>', 'YOUR-TEXTDOMAIN' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'submission_updated_messages' );