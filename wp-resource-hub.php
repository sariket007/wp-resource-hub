<?php
/**
 * Plugin Name: WP Resource Hub
 * Description: A modern, decoupled WordPress plugin architecture.
 * Version: 1.0.0
 * Author: Your Name
 */
if (!defined('ABSPATH')) {
exit;
}

/**Hook into the REST API initialization*/
add_action('rest_api_init', 'wrh_register_custom_endpoints');

function wrh_register_custom_endpoints() {
// Register route: /wp-json/wrh/v1/resource/{slug}
register_rest_route('wrh/v1', '/resource/(?P<slug>[a-zA-Z0-9-]+)', array(
'methods'             => WP_REST_Server::READABLE,
'callback'            => 'wrh_get_resource_by_slug',
'permission_callback' => '__return_true',
'args'                => array(
'slug' => array(
'validate_callback' => function($param, $request, $key) {
return is_string($param) && !is_numeric($param);
}
),
),
));
}

/**The Callback: Fetching data securely*/
function wrh_get_resource_by_slug($request) {
// Sanitize the incoming slug
$slug = sanitize_text_field($request->get_param('slug'));

// Query the database using the slug
$args = array(
'name'           => $slug,
'post_type'      => 'post',
'post_status'    => 'publish',
'posts_per_page' => 1
);

$query = new WP_Query($args);

if (empty($query->posts)) {
return new WP_Error('no_resource', 'Resource not found securely.', array('status' => 404));
}

$post = $query->posts[0];

// 3. The Payload: Format a clean, secure response
$data = array(
'title'   => get_the_title($post->ID),
'content' => get_the_content(null, false, $post->ID),
'slug'    => $post->post_name,
);

return rest_ensure_response($data);
}