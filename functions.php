<?php
add_action('post_edit_form_tag', 'add_multipart_form');
function add_multipart_form() {
    echo ' enctype="multipart/form-data"';
}

function sg_doc_schema_markers() {
	$markers = array ( 
		//add values as necessary for new tags, but add:
		//corresponding function to return formatted value, ie, sg_schema_process_TEXT($position, $value, $schema_string, $giveme); 
		'TEXT' => array (
			'format' => 'label',
			'example' => 'Title',
			'separators' => null,
			'notes'	=> "This is a simple text box, presented to the user, in which they can enter a string of text. <br /> The text will be sanitized before being written to the DB or filename."
			), 
		'DATE' => array (
			'format' => 'label:dateformat',
			'example' => 'Date of meeting: Y-m',
			'separators' => ":",
			'notes'	=> "This presents the user with a datepicker, with your chosen label next to it. <br />The date is formatted according to <a href='http://www.php.net//manual/en/function.date.php'> PHP Date syntax </a>."
			),  
		'CONST' => array (
			'format' => 'label',
			'example' => 'Minutes',
			'separators' => null,
			'notes' => "This allows you to enter a constant value, which will always be added to the new filename."
			),  
		'CHECK' => array (
			'format' => 'label:checked|unchecked',
			'example' => 'Revision?',
			'separators' => ": |",
			'notes' => "Presents users with a checkbox. Your label is appended to instruct whether the box should be checked. <br />The two options supplied are what will be appended to the filename. First option is checked, second is unchecked."
			), 
		);

		return $markers;
}



function sg_regex_schema_filename ($post_id, $giveme="string") {

	$the_schema_string = get_post_meta( $post_id, 'schema', true );

	$markers = sg_doc_schema_markers();

	$allmatches = array();

	foreach ($markers as $marker => $extra_info) {

		preg_match_all('#\['.$marker.'\](.*?)\[\/'.$marker.'\]#', $the_schema_string, $matches, PREG_OFFSET_CAPTURE);
		
		
		foreach ($matches[1] as $thematch) {
				$position = $thematch[1];
				$allmatches[$position]['marker'] = $marker; //note type of marker for later processing

				$allmatches[$position]['schema_string'] = $thematch[0]; //set schema_string
				$allmatches[$position]['offset_position'] = $position;
			}
		}

ksort($allmatches); //ensure that regex finds are put back into correct order


$allmatches = array_values($allmatches); //reset keys to start at 0 to avoid any problems with offset value changing

if ($giveme == "string") {
	$return = sg_schema_return_strings($allmatches);
	return $return;
}
elseif ($giveme == "formfield") {
	$return = sg_schema_return_formfields($allmatches);
	return $return;
}
elseif ($giveme == "metafields") {
	$metafields = array();
	foreach ($allmatches as $key => $field) {
		array_push($metafields, strtolower($field['marker'].$key));
	}
	return $metafields;
}

}

function sg_schema_return_strings($allmatches) {

$theformattedstring = "";

	foreach ($allmatches as $position => $match) {

		$return = call_user_func('sg_schema_process_'.$match['marker'], $position, $match, 'string');

		$theformattedstring = $theformattedstring.$return;		

	}


	return $theformattedstring;
}

function sg_schema_return_formfields($allmatches) {
	$theformfields = array();

	foreach ($allmatches as $position => $match) {

		$return = call_user_func('sg_schema_process_'.$match['marker'], $position, $match, 'formfield');

		array_push($theformfields, $return);		

	}
	return $theformfields;
}

//FUNCTIONS TO RETURN STRING OR FORMFIELD ARRAY FOR EACH TAG TYPE

function sg_schema_process_TEXT($position, $match, $giveme="string") {
	global $post;
	$field_type = 'text';
	$value = get_metadata('post', $post->ID, $field_type.$position, true);	


	if ($giveme == "string") {
		if ($value) {
			return sanitize_file_name( $value );
		}
		else {
			return "TEXT_ENTRY";
		}
	}

	elseif ($giveme == "formfield") {
		$formfield = array (
			'ID' => $position,
			'field_type' => $field_type,
			'label' => $match['schema_string'],
			'name' => $match['schema_string'],
			'value' => sanitize_file_name( $value ),
			);
		return $formfield;
	}

	
}

function sg_schema_process_DATE($position, $match, $giveme="string") {
	global $post;
	$field_type = 'date';
	$value = get_metadata('post', $post->ID, $field_type.$position, true);	

	$results = preg_split('/[:]/', $match['schema_string']);
	$label = $results[0];
	$format = $results[1];


	if ($giveme == "string") {
		if ($value) {
			$time = strtotime($value); //convert time back to unix time then reformat.
			return date($format, $time);
			// return $value;
		}
		else {
			return date($format, time());
		}
	}

	elseif ($giveme == "formfield") {
		$formfield = array (
			'ID' => $position,
			'field_type' => $field_type,
			'format' => $format,
			'label' => $label,
			'name' => $label,
			'value' => $value,
			);
		return $formfield;
	}
	
}

function sg_schema_process_CONST($position, $match, $giveme="string") {

	global $post;
	$field_type = 'const';
	$value = get_metadata('post', $post->ID, $field_type.$position, true);	

	if ($giveme == "string") {
		if ($value) {
			return sanitize_file_name( $value );
		}
		else {
			return sanitize_file_name ($match['schema_string']);
		}
	}
	elseif ($giveme == "formfield") {
		$formfield = array (
			'ID' => $position,
			'field_type' => $field_type,
			'label' => NULL,
			'name' => $match['schema_string'],
			'value' => sanitize_file_name( $match['schema_string'] ),
			);
		return $formfield;
	}
	
}
function sg_schema_process_CHECK($position, $match, $giveme="string") {

	global $post;
	$field_type = 'check';
	$options = preg_split('/[:|]/', $match['schema_string']); //includes label at this stage
	$label = array_shift($options); //remove label as separate variable, leaving just options in $options
	$value = get_metadata('post', $post->ID, $field_type.$position, true);
	if (!$value) {
		$value = $options[1]; //set the value to the 'unckecked' option
	}

	if ($giveme == "string") {
		if ($value == $options[0]) {
			$return = $options[0];
		}
		else {
			$return = $options[1];
		}
		return $return;
	}

	elseif ($giveme == "formfield") {

		$formfield = array (
			'ID' => $position,
			'field_type' => $field_type,
			'label' => $label,
			'name' => $label,
			'options' => $options,
			'value' => $value,
			);
		return $formfield;
	}
}


function sg_schema_output_formfield_html ($formfield, $post_id) {

//the hidden field is created to save all fields as metadata using /cpt/save_meta.php
$metadatafield = $formfield['field_type'].$formfield['ID'];
$thehiddenfield = "<input type='hidden' name='meta_save_type[]' value='".$metadatafield."' />";
$thehtml = "";
$metadatavalue = get_post_meta($post_id, $metadatafield, true);

$field_type = $formfield['field_type'];
$field_id = $formfield['ID'];
$label = $formfield['label'];
$thelabel = "<label for='".$field_type.$field_id."'>".$label.": </label>";
$type = "";
	
	switch ($field_type) {
		case 'const':
		$type = "hidden";
		$thelabel = "";
		$thehtml = "<input type='".$type."' name='".$field_type.$field_id."' value='".$formfield['name']."' />";
		break;

		case 'text':
		$type = "text";
		$thehtml = "<input type='".$type."' name='".$field_type.$field_id."' value='".$metadatavalue."' />";
		// $thelabel = $standard_label;
		break;

		case 'date';
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		// $thelabel = $standard_label;
		$thehtml = <<<EOD
		<input type="text" id="MyDate" name="$field_type$field_id" value="$metadatavalue"/>
		<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("#MyDate").datepicker({dateFormat : "dd-mm-yy"});
		});
		</script>
EOD;
		break;

		case 'check';
		$checked = "";
		if ($metadatavalue == $formfield['options'][0]) {
			$checked = "checked";
		}

		$thehtml = "<input type='checkbox' name='".$metadatafield."' value='".$formfield['options'][0]."' ".$checked." />";

		break;
		
		default:
			# code...
		break;
	}

	return $thehiddenfield.$thelabel.$thehtml;

}