<?php
/**
 * Plugin Name: Gamestore General
 * Description: Core Code for Gamestore
 * Version: 1.0
 * Author: Vitaliy Galata
 * Author URI: https://github.com/vitaliygalata1986
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

 function gamestore_remove_dashboard_widgets(){
    global $wp_meta_boxes;

    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
    unset($wp_meta_boxes['dashboard']['normal']['high']['rank_math_dashboard_widget']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health']);
 }
 add_action('wp_dashboard_setup', 'gamestore_remove_dashboard_widgets');

// Allow SVG uploads
function gamestore_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'gamestore_mime_types');

// Fix SVG display in media library
function gamestore_fix_svg() {
    echo '<style>
      .attachment-266x266, .thumbnail img {
          width: 100% !important;
          height: auto !important;
      }
  </style>';
}
add_action('admin_head', 'gamestore_fix_svg');

// Register Custom Post Type "News"
function gamestore_register_news_post_type() {
    $labels = array(
        'name'                  => _x('News', 'Post Type General Name', 'gamestore'),
        'singular_name'         => _x('News', 'Post Type Singular Name', 'gamestore'),
        'menu_name'             => __('News', 'gamestore'),
        'name_admin_bar'        => __('News', 'gamestore'),
        'archives'              => __('News Archives', 'gamestore'),
        'attributes'            => __('News Attributes', 'gamestore'),
        'parent_item_colon'     => __('Parent News:', 'gamestore'),
        'all_items'             => __('All News', 'gamestore'),
        'add_new_item'          => __('Add New News', 'gamestore'),
        'add_new'               => __('Add New', 'gamestore'),
        'new_item'              => __('New News', 'gamestore'),
        'edit_item'             => __('Edit News', 'gamestore'),
        'update_item'           => __('Update News', 'gamestore'),
        'view_item'             => __('View News', 'gamestore'),
        'view_items'            => __('View News', 'gamestore'),
        'search_items'          => __('Search News', 'gamestore'),
        'not_found'             => __('Not found', 'gamestore'),
        'not_found_in_trash'    => __('Not found in Trash', 'gamestore'),
        'featured_image'        => __('Featured Image', 'gamestore'),
        'set_featured_image'    => __('Set featured image', 'gamestore'),
        'remove_featured_image' => __('Remove featured image', 'gamestore'),
        'use_featured_image'    => __('Use as featured image', 'gamestore'),
        'insert_into_item'      => __('Insert into news', 'gamestore'),
        'uploaded_to_this_item' => __('Uploaded to this news', 'gamestore'),
        'items_list'            => __('News list', 'gamestore'),
        'items_list_navigation' => __('News list navigation', 'gamestore'),
        'filter_items_list'     => __('Filter news list', 'gamestore'),
    );
    $args = array(
        'label'                 => __('News', 'gamestore'),
        'description'           => __('News Description', 'gamestore'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields'),
        'taxonomies'            => array('news_category'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_rest'          => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
    );
    register_post_type('news', $args);
}
add_action('init', 'gamestore_register_news_post_type', 0);

// Register Custom Taxonomy "News Category"
function gamestore_register_news_category_taxonomy() {
    $labels = array(
        'name'                       => _x('News Categories', 'Taxonomy General Name', 'gamestore'),
        'singular_name'              => _x('News Category', 'Taxonomy Singular Name', 'gamestore'),
        'menu_name'                  => __('News Category', 'gamestore'),
        'all_items'                  => __('All Categories', 'gamestore'),
        'parent_item'                => __('Parent Category', 'gamestore'),
        'parent_item_colon'          => __('Parent Category:', 'gamestore'),
        'new_item_name'              => __('New Category Name', 'gamestore'),
        'add_new_item'               => __('Add New Category', 'gamestore'),
        'edit_item'                  => __('Edit Category', 'gamestore'),
        'update_item'                => __('Update Category', 'gamestore'),
        'view_item'                  => __('View Category', 'gamestore'),
        'separate_items_with_commas' => __('Separate categories with commas', 'gamestore'),
        'add_or_remove_items'        => __('Add or remove categories', 'gamestore'),
        'choose_from_most_used'      => __('Choose from the most used', 'gamestore'),
        'popular_items'              => __('Popular Categories', 'gamestore'),
        'search_items'               => __('Search Categories', 'gamestore'),
        'not_found'                  => __('Not Found', 'gamestore'),
        'no_terms'                   => __('No categories', 'gamestore'),
        'items_list'                 => __('Categories list', 'gamestore'),
        'items_list_navigation'      => __('Categories list navigation', 'gamestore'),
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
        'show_in_rest'               => true,
    );
    register_taxonomy('news_category', array('news'), $args);
}
add_action('init', 'gamestore_register_news_category_taxonomy', 0);