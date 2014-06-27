<?php

//Define sg_doc types based off sg_doc_schema slugs.

add_action( 'init', 'register_cpt_sg_doc' );

function register_cpt_sg_doc() {

    $labels = array( 
        'name' => _x( 'Documents', 'sg_doc' ),
        'singular_name' => _x( 'Document', 'sg_doc' ),
        'add_new' => _x( 'Add New Document', 'sg_doc' ),
        'add_new_item' => _x( 'Add New Document', 'sg_doc' ),
        'edit_item' => _x( 'Edit Document', 'sg_doc' ),
        'new_item' => _x( 'New Document', 'sg_doc' ),
        'view_item' => _x( 'View Document', 'sg_doc' ),
        'search_items' => _x( 'Search Document', 'sg_doc' ),
        'not_found' => _x( 'No documents found', 'sg_doc' ),
        'not_found_in_trash' => _x( 'No documents found in Trash', 'sg_doc' ),
        'parent_item_colon' => _x( 'Parent document', 'sg_doc' ),
        'all_items' => _x( 'All Documents', 'sg_doc' ),
        );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'description' => 'Allow uploads of documents according to predefined naming conventions.',
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => 'edit.php?post_type=sg_doc_schema',
        'menu_position' => 20,
        'publicly_queryable' => true,
        'exclude_from_search' => true,
        'has_archive' => false,
        'query_var' => 'schema', // maybe to pass sg_doc_schema?
        'can_export' => true,
        'rewrite' => false,
        'capability_type' => 'post',
        'supports' => false,
        );

    register_post_type( 'sg_doc', $args );
}

add_action('admin_menu', 'register_sg_docs_menus');

function register_sg_docs_menus() {

  add_submenu_page( 'edit.php?post_type=sg_doc_schema', 'Add Documents', 'Add Document', 'publish_posts', 'sg_doc_redirect', 'sg_doc_redirect' );

}

function sg_doc_redirect () {
	$doc_schemas = get_posts(  
		array(
			'numberposts'		=>	0,
			'orderby'			=>	'title',
			'order'				=>	'ASC',
			'post_type'			=>	'sg_doc_schema',
			'post_status'		=>	'publish' )
		);
	$page_title = get_admin_page_title();
    ?>
    <div class="wrap">
        <h2><?php echo $page_title; ?></h2>
        <p>Upload New:</p>
        <ul>
            <?php
            foreach ($doc_schemas as $doc_schema) {
                $url = add_query_arg('schema', $doc_schema->ID, 'post-new.php?post_type=sg_doc');
                echo "<li><a href='".$url."'>".$doc_schema->post_title."</a></li>";
            }
        echo "</ul></div>";
        }

// hide 'Add New' button so users are forced through the preliminary screen
function sg_doc_hide_add_new() {
    // if (!isset($_GET['post_type'])) {
    //     return;
    // }
    // $query = $_GET['post_type'];
    //     if('sg_doc' == $query){
          echo '<style type="text/css">
            body.post-type-sg_doc .add-new-h2 {display:none;}
            </style>';}
    // }
add_action('admin_head', 'sg_doc_hide_add_new');

        ?>