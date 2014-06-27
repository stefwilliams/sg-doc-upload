<?php
//Course Meta fields

add_action('admin_menu', 'sg_doc_meta');

function sg_doc_meta() {
	if (isset($_GET['schema'])) {
		$schema_id = $_GET['schema'];
	}
	elseif (isset($_GET['post'])) {
		$post_id = $_GET['post'];
		$schema_id = get_post_meta($post_id, 'schema_applied', true);
	}
	if (isset($schema_id)) {
	$schema_name = get_the_title($schema_id);
	add_meta_box('sg_doc_meta_box', $schema_name, 'sg_doc_meta_inputs', 'sg_doc', 'normal', 'high', array('schema_id' =>$schema_id));
	}
}

function sg_doc_meta_inputs($post, $schema_id){ 


	$schema_id = $schema_id['args']['schema_id'];

	$post_id = $post->ID;
	$formfields = sg_regex_schema_filename ($schema_id, "formfield");
	$current_sg_schema = $schema_id;
	$meta_type_sg_schema = 'schema_applied';
	$current_title = get_the_title($post_id);
	$meta_type_sg_file = 'file_upload';
	wp_nonce_field( 'save_meta', 'save_meta_nonce' ); ?>

	<div class="wrap">
		<fieldset>
			<input type="hidden" name="meta_save_type[]" value="<?php echo $meta_type_sg_schema; ?>" />
			<input type="hidden" name="<?php echo $meta_type_sg_schema; ?>" value="<?php echo $current_sg_schema; ?>" />
		</fieldset>

		<?php
		foreach ($formfields as $formfield) {
			echo "<fieldset>";
			$fieldhtml = sg_schema_output_formfield_html($formfield, $post_id);
			echo $fieldhtml;
			echo "</fieldset>";
		}

		$existing_upload = get_post_meta( $post_id, $meta_type_sg_file, true );
		if ($existing_upload) {
			$existing_upload = maybe_unserialize($existing_upload);
			?>
			<p>Attached document: </p>
			<p><a href="<?php echo $existing_upload['url']; ?>"><?php echo $existing_upload['name']; ?></a> (<?php echo round(($existing_upload['filesize'])/1000, 2); ?> kb)</p>
			<?php 
		}
		?>

		<p><label for="file_upload">Upload new document:</label><br/>
			<?php  if ($existing_upload) { ?>
			<strong>Warning! Uploading a new document will DELETE any existing attached files</strong></p>
			<?php } ?>
			<input type="hidden" name="file_upload[]" value="<?php echo $meta_type_sg_file; ?>" />
			<input type="file" name="<?php echo $meta_type_sg_file; ?>" id="<?php echo $meta_type_sg_file; ?>" />

<?php
if(!empty($download_id) && $download_id != '0') {
	?>
	<p><a href="<?php wp_get_attachment_url($download_id);?>">View document</a></p>
	<?php    }


	?>


</div>

<?php

}


?>