<?php

//Define sg_doc_schema content type.

add_action( 'init', 'register_cpt_sg_doc_schema' );

function register_cpt_sg_doc_schema() {

    $labels = array( 
        'name' => _x( 'Document schemas', 'sg_doc_schema' ),
        'singular_name' => _x( 'Document schema', 'sg_doc_schema' ),
        'add_new' => _x( 'Add New Schema', 'sg_doc_schema' ),
        'add_new_item' => _x( 'Add New Document schema', 'sg_doc_schema' ),
        'edit_item' => _x( 'Edit Document schemas', 'sg_doc_schema' ),
        'new_item' => _x( 'New Document schemas', 'sg_doc_schema' ),
        'view_item' => _x( 'View Document schema', 'sg_doc_schema' ),
        'search_items' => _x( 'Search Document schemas', 'sg_doc_schema' ),
        'not_found' => _x( 'No document schemas found', 'sg_doc_schema' ),
        'not_found_in_trash' => _x( 'No document schemas found in Trash', 'sg_doc_schema' ),
        'parent_item_colon' => _x( 'Parent document schemas:', 'sg_doc_schema' ),
        'menu_name' => _x( 'Document Uploads', 'sg_doc_schema' ),
        'all_items' => _x( 'Document Schemas', 'sg_doc_schema' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'description' => 'Allow uploads of documents according to predefined naming conventions.',
        //'supports' => array( 'title' ),        
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 20,
        'menu_icon' => 'dashicons-media-document',
        // 'show_in_nav_menus' => false,
        'publicly_queryable' => true,
        'exclude_from_search' => true,
        'has_archive' => false,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => false,
        'capability_type' => 'post',
        'supports' => 'title',
        // 'capabilities' => array(
        //     'create_posts' => false, )
    );

    register_post_type( 'sg_doc_schema', $args );
}