<?php

function sg_doc_list( $atts ) {
    $a = shortcode_atts( array(
        'schema_id' => 'all',
        'limit' => '-1',
        'order' => 'DESC',
    ), $atts );

	if ('all' == $a['schema_id']) {
		$schema_id = "";
		$compare = "NOT IN";
	}
	else {
		$schema_id = $a['schema_id'];
		$compare = "IN";
	}

$args = array (
	// 'name' => $name,
	'post_type' => 'sg_doc',
	'post_status' => 'publish',
	'posts_per_page' => $a['limit'],
	'order' => $a['order'],
	'orderby' => 'title',
    'meta_query' => array (
    	array(
         'key' => 'schema_applied',		//(string) - Custom field key.
         'value' => $schema_id, 		//(string/array) - Custom field value (Note: Array support is limited to a compare value of 'IN', 'NOT IN', 'BETWEEN', or 'NOT BETWEEN')
         'type' => 'NUMERIC',			//(string) - Custom field type. Possible values are 'NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED'. Default value is 'CHAR'.
         'compare' => $compare,			//(string) - Operator to test. Possible values are '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'. Default value is '='.
       ),	
    )
);




$the_docs = new WP_Query( $args );

if ($the_docs->post_count == 0) {
	return "No documents have been uploaded yet.";
}

$output = '<ul>';
// The Loop
	while ( $the_docs->have_posts() ) {
	$the_docs->the_post();

		$post_id = get_the_ID();
		
		$schema_id = get_post_meta( $post_id, 'schema_applied', true );
		$uploaded_file = unserialize(get_post_meta( $post_id, 'file_upload', true ));
		$display_schema = get_post_meta( $schema_id, 'display', true );

		$filesize = number_format($uploaded_file['filesize'] / 1000, 2) . "k";

		if ($display_schema) {
			$the_title = sg_doc_get_the_title_link($post_id, $display_schema);
		}
		else {
			$the_title = $uploaded_file['name'];
		}

		$output .= "<li><a href='".$uploaded_file['url']."'>" . $the_title ."</a>". " (".$filesize.")</li>";
	}

/* Restore original Post Data */
wp_reset_postdata();

$output .= '</ul>';

return $output;

}

add_shortcode( 'sg_docs', 'sg_doc_list' );


function sg_doc_get_the_title_link($post_id, $display_schema) {
// search the whole display string for lookup codes
	preg_match_all('#\[(.*?)]#', $display_schema, $matches, PREG_SET_ORDER);
	foreach ($matches as $key => $match) {
		//search $matches for colons (indicating formatting)	
		$results = preg_split('/:/', $match[1]);
		foreach ($results as $resultkey => $result) {
			//and add to $matches in the correct place
			array_push($matches[$key], $result);
			// single $match now looks like:
			/*
			    [0] => Array
		        (
		            [0] => [date1:d M Y] (original code in string)
		            [1] => date1:d M Y (content of code)
		            [2] => date1 (the metadata field to look up)
		            [3] => d M Y (format for metadata, if exists)
		        )
			*/
		}
	}
	foreach ($matches as $key => $match) {
		// find metadata value
		$metafield = $match[2];
		$metavalue = get_post_meta( $post_id, $metafield, true );
		// format metadata value if it exists
		if (array_key_exists(3, $match)) {
			$format = $match[3];
			$metavalue = strtotime($metavalue);
			$metavalue = date($format, $metavalue);
		}
		// replace tags in string with metadata values
	$display_schema = str_replace($match[0], $metavalue, $display_schema);
	}
	return $display_schema;
}