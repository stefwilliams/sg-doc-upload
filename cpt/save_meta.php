<?php
//this is the main function that saves the post meta for all the custom types.

function save_sg_doc_meta ($post_id) {

global $post_type;

// error_log(var_export($post_id,true));

if ($post_type != ("sg_doc_schema" || "sg_doc")) {
    return;
}
   if ( !isset( $_POST['save_meta_nonce'] ) || !wp_verify_nonce( $_POST['save_meta_nonce'], 'save_meta' ) ){
       return $post_id;

   }
    //use the hidden meta_save_type field to pull in the array of meta content to be saved.
    $meta_save_types = $_POST['meta_save_type'];

    if (is_array($meta_save_types)) {
        foreach ($meta_save_types as $meta_save_type) {
             //save custom_meta values
            $new_sgmeta_value = $_POST[$meta_save_type];
            /* Get the meta key. */
            $sgmeta_key = $meta_save_type;
            /* Get the meta value of the custom field key. */
            $sgmeta_value = get_post_meta( $post_id, $sgmeta_key, true );
            /* If a new meta value was added and there was no previous value, add it. */
            if ( $new_sgmeta_value && '' == $sgmeta_value ){
                add_post_meta( $post_id, $sgmeta_key, $new_sgmeta_value, true );
            }
            /* If the new meta value does not match the old value, update it. */
            elseif ( $new_sgmeta_value && $new_sgmeta_value != $sgmeta_value ) {
                update_post_meta( $post_id, $sgmeta_key, $new_sgmeta_value );
                if ($post_type == "sg_doc") {
                    sg_doc_rename_existing_file($post_id);
                }
            }
            /* If there is no new meta value but an old value exists, delete it. */
            elseif ( '' == $new_sgmeta_value && $sgmeta_value ) {
                delete_post_meta( $post_id, $sgmeta_key, $sgmeta_value );
            }
        }
    }
    if ($post_type == "sg_doc") {
        save_sg_doc_file($post_id, $post);
    }
}

function sg_doc_rename_existing_file($post_id) {
    //get existing upload
    $existing_upload = unserialize( get_post_meta( $post_id, 'file_upload', true ) );
    //get file pathinfo
    $file_pathinfo = pathinfo($existing_upload['file']);
    //get url path info
    $url_pathinfo = pathinfo($existing_upload['url']);
    //get schema applied
    $schema_id = get_post_meta($post_id, 'schema_applied', true);
    //get filename based on new meta values
    $new_filename = sg_regex_schema_filename ($schema_id, 'string');

    // write new array to include details of new filename
    $modified_upload = array (
        'file' => $file_pathinfo['dirname'].'/'.$new_filename.'.'.$file_pathinfo['extension'],
        'url' => $url_pathinfo['dirname'].'/'.$new_filename.'.'.$url_pathinfo['extension'],
        'type' => $existing_upload['type'],
        'filesize' => $existing_upload['filesize'],
        'name' => $new_filename.'.'.$url_pathinfo['extension'],
    );

    // update meta of post with new array (serialized)
    update_post_meta( $post_id, 'file_upload', serialize($modified_upload));
    //rename the file itself
    rename($existing_upload['file'], $modified_upload['file']);
    //rename the post
    $postarr = array(
      'ID' => $post_id,
      'post_title' => $new_filename,
    );
    $newtitle = wp_update_post( $postarr );    
}


function save_sg_doc_file($post_id) {

$uploaded_file = $_POST['file_upload'][0];
$upload_key = 'file_upload';
$existing_upload = get_post_meta( $post_id, $upload_key, true );  
    $schema_id = $_POST['schema_applied'];

if($_FILES[$uploaded_file]['size'] > 0 ) {
    $file = $_FILES[$uploaded_file];


    add_filter( 'upload_dir', 'change_upload_dir' );
    // ADD NEW_NAME to the file array, so we can use this in wp_handle_upload_prefilter
    $new_filename = sg_regex_schema_filename($schema_id, 'string');
    $upload = wp_handle_upload( $file, array('test_form' => false, $file['new_name'] = $new_filename, ) );
    remove_filter( 'upload_dir', 'change_upload_dir' );

    if(!isset($upload['error']) && isset($upload['file'])) {
        $upload = array_merge($upload, array('filesize'=>$file['size']));
        $upload = array_merge($upload, array('name'=>$file['name']));
        $upload = serialize($upload);

        //delete existing upload and remove meta if a new file is uploaded.
        if ($upload != '') {
            // error_log(var_export($upload, true));
            if ($existing_upload) {
                $existing_upload = unserialize($existing_upload);
                // error_log(var_export($existing_upload['file'], true));
                unlink($existing_upload['file']); 

                // error_log($go);
                delete_post_meta( $post_id, $upload_key, $existing_upload );
            }
            update_post_meta( $post_id, $upload_key, $upload);
        }

        $postarr = array(
            'ID' => $post_id,
            'post_title' => $new_filename,
            );

        $newtitle = wp_update_post( $postarr );
        // error_log("new title applied?");
        // error_log($newtitle);
    }
}

}
function change_upload_dir($upload_dir) {
    global $post;
    $schema_applied = get_post_meta ($post->ID, 'schema_applied', true);
    $upload_subdir = get_post_meta($schema_applied, 'subdir', true);

if ($upload_subdir) {
    $upload_subdir = '/sg-docs/' . $upload_subdir;
}
else {
    $upload_subdir = "/sg-docs";
}
    error_log(var_export($post, true));
    error_log(var_export($upload_subdir, true));
    $upload_dir['path']   = $upload_dir['basedir'] . $upload_dir['subdir'] . $upload_subdir;
    $upload_dir['url']    = $upload_dir['baseurl'] . $upload_dir['subdir'] . $upload_subdir;

    error_log(var_export($upload_dir, true));
    return $upload_dir;
}
/* Save post meta on the 'save_post' hook. */
add_action( 'save_post', 'save_sg_doc_meta', 10, 1 );

function sg_schema_change_filename($file) {
    $info = pathinfo($file['name']);
    $ext  = empty($info['extension']) ? '' : '.' . $info['extension'];
    $new_filename = $file['new_name'].$ext;
    $file['name'] =  $new_filename;
    return $file;
}
add_filter('wp_handle_upload_prefilter', 'sg_schema_change_filename', 10);


function sg_doc_delete_attached_files($post_id) {
    global $post_type;
    if ($post_type != 'sg_doc') {
        return;
    }
    // error_log(var_export($post_id,true));

    $existing_upload = unserialize(get_post_meta( $post_id, 'file_upload', true  ));
    // error_log(var_export($existing_upload,true));
    unlink($existing_upload['file']);
}



add_action( 'before_delete_post', 'sg_doc_delete_attached_files', 10, 1 );


?>