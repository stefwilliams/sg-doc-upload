<?php

//Course Meta fields

add_action('admin_menu', 'sg_doc_schema_meta');

function sg_doc_schema_meta() {
	add_meta_box('sg_doc_schema_meta_box', 'Filename Schema', 'sg_doc_schema_meta_inputs', 'sg_doc_schema', 'normal', 'high');
	add_meta_box('sg_doc_schema_meta_helpbox', 'Schema Setup Help', 'sg_doc_schema_help', 'sg_doc_schema', 'normal', 'default');
}

function sg_doc_schema_help () {
	$markers = sg_doc_schema_markers();

echo "The valid marker tags for creating document saving schemas are <table class='widefat fixed' cellspacing='0'><th>Tag</th><th>Content Format</th><th>Example</th><th>Required Separators</th><th>Notes</th>";
	foreach ($markers as $marker => $help) {
		echo "<tr>";
		echo "<td>[".$marker."] ... [/".$marker."]</td>";
		echo "<td>".$help['format']."</td>";
		echo "<td>".$help['example']."</td>";
		echo "<td>".$help['separators']."</td>";
		echo "<td>".$help['notes']."</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "<p><strong>Important</strong> The format of the content inside the marker tags must be correct using the correct separators, otherwise the file will not be renamed correctly...</p>";
	echo "<p>Once you have saved your schema, the 'Example filename' will be filled in using the data you entered... You can use this to check if your schema is correct.</p>";
	echo "<p>Finally, you can change the folder in which the uploaded file will be saved, allowing each schema to save to a different folder.</p>";
}

function sg_doc_schema_meta_inputs(){ ?>
<?php
global $post;
$post_id = $post->ID;
$current_sg_doc_schema = get_post_meta( $post_id, 'schema', true );
$meta_type_sg_doc_schema = 'schema';

$current_sg_display_schema = get_post_meta( $post_id, 'display', true );
$meta_type_sg_display_schema = 'display';

$current_sg_doc_schema_directory = get_post_meta( $post_id, 'subdir', true );
$meta_type_sg_doc_schema_directory = 'subdir';

$upload_dir = wp_upload_dir();
?>



<?php wp_nonce_field( 'save_meta', 'save_meta_nonce' ); ?>
<div class="wrap">
	<fieldset>		
			<input type="hidden" name="meta_save_type[]" value="<?php echo $meta_type_sg_doc_schema; ?>" />
			<textarea style="width:100%" rows="5" cols="50" name="<?php echo $meta_type_sg_doc_schema; ?>"><?php echo $current_sg_doc_schema; ?></textarea>		
			<input type="hidden" name="meta_save_type[]" value="<?php echo $meta_type_sg_doc_schema_directory; ?>" />
			<label for "<?php echo $meta_type_sg_doc_schema_directory; ?>">Save in <br /><?php echo $upload_dir['url']; ?>/sg-docs/</label>
			<input type="text" name="<?php echo $meta_type_sg_doc_schema_directory; ?>" value="<?php echo $current_sg_doc_schema_directory; ?>" />
	</fieldset>
	<hr />
	Example filename: <strong><?php echo sg_regex_schema_filename($post_id, 'string'); ?>.doc </strong>
	<hr />

	<?php $metafields = sg_regex_schema_filename($post_id, 'metafields');
	echo "<h3>Name Display Schema</h3>";
	if (empty($metafields)) {
		echo "Add some marker tags to the Filename Schema, and save this post. Then you will be able to see the available metafields that you can use to format the front-end display.";
	}
	else {
		echo "You may now use the tags below to define the front-end display schema. <br /> You can enter plain text which will act as constants. You can change the format of 'date' fields by using the following syntax: [date(x):Y-m]. All other fields will be echoed exactly as they go into the database.";
		echo "<ul>";
		foreach ($metafields as $metafield) {
			echo "<li>[".$metafield."]</li>";
		}
		echo "</ul>";
		?>
	<fieldset>		
			<input type="hidden" name="meta_save_type[]" value="<?php echo $meta_type_sg_display_schema; ?>" />
			<textarea style="width:100%" rows="5" cols="50" name="<?php echo $meta_type_sg_display_schema; ?>"><?php echo $current_sg_display_schema; ?></textarea>		
	</fieldset>
		<?php 
	}

	?>
</div>

<?php
}


?>