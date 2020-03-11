<?php

function grassblade_get_groups( $args ) {
    global $wp_xmlrpc_server;
    $wp_xmlrpc_server->escape( $args );

    $blog_id  = $args[0];
    $username = $args[1];
    $password = $args[2];

    if ( ! $user = $wp_xmlrpc_server->login( $username, $password ) )
        return $wp_xmlrpc_server->error;
    
    if(user_can($user, "manage_options") || user_can("connect_grassblade_lrs")) {
        $params = $args[3];
        return apply_filters("grassblade_groups", array(), $params);
    }

    return;
}
function grassblade_get_groups_rest_api( $d ) {
    $params = array();

    if(!empty($_REQUEST["id"]) && is_numeric($_REQUEST["id"]))
        $params["id"] = $_REQUEST["id"];

    if(!empty($_REQUEST["posts_per_page"]) && is_numeric($_REQUEST["posts_per_page"]))
        $params["posts_per_page"] = $_REQUEST["posts_per_page"];

    if(!empty($_REQUEST["leaders_list"]) && is_numeric($_REQUEST["leaders_list"]))
        $params["leaders_list"] = $_REQUEST["leaders_list"];

    if(!empty($_REQUEST["users_list"]) && is_numeric($_REQUEST["users_list"]))
        $params["users_list"] = $_REQUEST["users_list"];

    return apply_filters("grassblade_groups", array(), $params);
}
function grassblade_get_group_leaders($group) {
    return apply_filters("grassblade_group_leaders", array(), $group);
}
function grassblade_get_group_users($group) {
    return apply_filters("grassblade_group_users", array(), $group);
}
function grassblade_xmlrpc_methods( $methods ) {
    $methods['grassblade.getGroups'] = 'grassblade_get_groups';
    return $methods;   
}
add_filter( 'xmlrpc_methods', 'grassblade_xmlrpc_methods');

add_action( 'rest_api_init', function () {
  register_rest_route( 'grassblade/v1', '/getGroups', array(
    'methods' => 'GET',
    'callback' => 'grassblade_get_groups_rest_api',
    'permission_callback' => function () {
      return current_user_can( 'connect_grassblade_lrs' ) ||  current_user_can( 'manage_options' );
    }
  ) );
} );
