<?php

/**
 * Registers the `submission_category` taxonomy,
 * for use with 'submission'.
 */
function submission_category_init() {
	register_taxonomy( 'submission_category', array( 'submission' ), array(
		'hierarchical'      => false,
		'public'            => true,
		'show_in_nav_menus' => true,
		'show_ui'           => true,
		'show_admin_column' => false,
		'query_var'         => true,
		'rewrite'           => true,
		'capabilities'      => array(
			'manage_terms'  => 'edit_posts',
			'edit_terms'    => 'edit_posts',
			'delete_terms'  => 'edit_posts',
			'assign_terms'  => 'edit_posts',
		),
		'labels'            => array(
			'name'                       => __( 'submission categories', 'YOUR-TEXTDOMAIN' ),
			'singular_name'              => _x( 'submission category', 'taxonomy general name', 'YOUR-TEXTDOMAIN' ),
			'search_items'               => __( 'Search submission categories', 'YOUR-TEXTDOMAIN' ),
			'popular_items'              => __( 'Popular submission categories', 'YOUR-TEXTDOMAIN' ),
			'all_items'                  => __( 'All submission categories', 'YOUR-TEXTDOMAIN' ),
			'parent_item'                => __( 'Parent submission category', 'YOUR-TEXTDOMAIN' ),
			'parent_item_colon'          => __( 'Parent submission category:', 'YOUR-TEXTDOMAIN' ),
			'edit_item'                  => __( 'Edit submission category', 'YOUR-TEXTDOMAIN' ),
			'update_item'                => __( 'Update submission category', 'YOUR-TEXTDOMAIN' ),
			'view_item'                  => __( 'View submission category', 'YOUR-TEXTDOMAIN' ),
			'add_new_item'               => __( 'Add New submission category', 'YOUR-TEXTDOMAIN' ),
			'new_item_name'              => __( 'New submission category', 'YOUR-TEXTDOMAIN' ),
			'separate_items_with_commas' => __( 'Separate submission categories with commas', 'YOUR-TEXTDOMAIN' ),
			'add_or_remove_items'        => __( 'Add or remove submission categories', 'YOUR-TEXTDOMAIN' ),
			'choose_from_most_used'      => __( 'Choose from the most used submission categories', 'YOUR-TEXTDOMAIN' ),
			'not_found'                  => __( 'No submission categories found.', 'YOUR-TEXTDOMAIN' ),
			'no_terms'                   => __( 'No submission categories', 'YOUR-TEXTDOMAIN' ),
			'menu_name'                  => __( 'submission categories', 'YOUR-TEXTDOMAIN' ),
			'items_list_navigation'      => __( 'submission categories list navigation', 'YOUR-TEXTDOMAIN' ),
			'items_list'                 => __( 'submission categories list', 'YOUR-TEXTDOMAIN' ),
			'most_used'                  => _x( 'Most Used', 'submission_category', 'YOUR-TEXTDOMAIN' ),
			'back_to_items'              => __( '&larr; Back to submission categories', 'YOUR-TEXTDOMAIN' ),
		),
		'show_in_rest'      => true,
		'rest_base'         => 'submission_category',
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	) );

}
add_action( 'init', 'submission_category_init' );

/**
 * Sets the post updated messages for the `submission_category` taxonomy.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `submission_category` taxonomy.
 */
function submission_category_updated_messages( $messages ) {

	$messages['submission_category'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => __( 'submission category added.', 'YOUR-TEXTDOMAIN' ),
		2 => __( 'submission category deleted.', 'YOUR-TEXTDOMAIN' ),
		3 => __( 'submission category updated.', 'YOUR-TEXTDOMAIN' ),
		4 => __( 'submission category not added.', 'YOUR-TEXTDOMAIN' ),
		5 => __( 'submission category not updated.', 'YOUR-TEXTDOMAIN' ),
		6 => __( 'submission categories deleted.', 'YOUR-TEXTDOMAIN' ),
	);

	return $messages;
}
add_filter( 'term_updated_messages', 'submission_category_updated_messages' );
