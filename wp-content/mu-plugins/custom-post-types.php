<?php

/**
 * Registers the `post` post type.
 */
function post_init() {
	register_post_type( 'post', array(
		'labels'                => array(
			'name'                  => __( 'posts', 'YOUR-TEXTDOMAIN' ),
			'singular_name'         => __( 'post', 'YOUR-TEXTDOMAIN' ),
			'all_items'             => __( 'All posts', 'YOUR-TEXTDOMAIN' ),
			'archives'              => __( 'post Archives', 'YOUR-TEXTDOMAIN' ),
			'attributes'            => __( 'post Attributes', 'YOUR-TEXTDOMAIN' ),
			'insert_into_item'      => __( 'Insert into post', 'YOUR-TEXTDOMAIN' ),
			'uploaded_to_this_item' => __( 'Uploaded to this post', 'YOUR-TEXTDOMAIN' ),
			'featured_image'        => _x( 'Featured Image', 'post', 'YOUR-TEXTDOMAIN' ),
			'set_featured_image'    => _x( 'Set featured image', 'post', 'YOUR-TEXTDOMAIN' ),
			'remove_featured_image' => _x( 'Remove featured image', 'post', 'YOUR-TEXTDOMAIN' ),
			'use_featured_image'    => _x( 'Use as featured image', 'post', 'YOUR-TEXTDOMAIN' ),
			'filter_items_list'     => __( 'Filter posts list', 'YOUR-TEXTDOMAIN' ),
			'items_list_navigation' => __( 'posts list navigation', 'YOUR-TEXTDOMAIN' ),
			'items_list'            => __( 'posts list', 'YOUR-TEXTDOMAIN' ),
			'new_item'              => __( 'New post', 'YOUR-TEXTDOMAIN' ),
			'add_new'               => __( 'Add New', 'YOUR-TEXTDOMAIN' ),
			'add_new_item'          => __( 'Add New post', 'YOUR-TEXTDOMAIN' ),
			'edit_item'             => __( 'Edit post', 'YOUR-TEXTDOMAIN' ),
			'view_item'             => __( 'View post', 'YOUR-TEXTDOMAIN' ),
			'view_items'            => __( 'View posts', 'YOUR-TEXTDOMAIN' ),
			'search_items'          => __( 'Search posts', 'YOUR-TEXTDOMAIN' ),
			'not_found'             => __( 'No posts found', 'YOUR-TEXTDOMAIN' ),
			'not_found_in_trash'    => __( 'No posts found in trash', 'YOUR-TEXTDOMAIN' ),
			'parent_item_colon'     => __( 'Parent post:', 'YOUR-TEXTDOMAIN' ),
			'menu_name'             => __( 'posts', 'YOUR-TEXTDOMAIN' ),
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
		'rest_base'             => 'post',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	) );

}
add_action( 'init', 'post_init' );

/**
 * Sets the post updated messages for the `post` post type.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `post` post type.
 */
function post_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['post'] = array(
		0  => '', // Unused. Messages start at index 1.
		/* translators: %s: post permalink */
		1  => sprintf( __( 'post updated. <a target="_blank" href="%s">View post</a>', 'YOUR-TEXTDOMAIN' ), esc_url( $permalink ) ),
		2  => __( 'Custom field updated.', 'YOUR-TEXTDOMAIN' ),
		3  => __( 'Custom field deleted.', 'YOUR-TEXTDOMAIN' ),
		4  => __( 'post updated.', 'YOUR-TEXTDOMAIN' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'post restored to revision from %s', 'YOUR-TEXTDOMAIN' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		/* translators: %s: post permalink */
		6  => sprintf( __( 'post published. <a href="%s">View post</a>', 'YOUR-TEXTDOMAIN' ), esc_url( $permalink ) ),
		7  => __( 'post saved.', 'YOUR-TEXTDOMAIN' ),
		/* translators: %s: post permalink */
		8  => sprintf( __( 'post submitted. <a target="_blank" href="%s">Preview post</a>', 'YOUR-TEXTDOMAIN' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		/* translators: 1: Publish box date format, see https://secure.php.net/date 2: Post permalink */
		9  => sprintf( __( 'post scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview post</a>', 'YOUR-TEXTDOMAIN' ),
		date_i18n( __( 'M j, Y @ G:i', 'YOUR-TEXTDOMAIN' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		/* translators: %s: post permalink */
		10 => sprintf( __( 'post draft updated. <a target="_blank" href="%s">Preview post</a>', 'YOUR-TEXTDOMAIN' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'post_updated_messages' );