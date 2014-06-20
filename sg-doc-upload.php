<?php
/*
Plugin Name: SG Document Upload 
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Allow uploads of documents according to predefined naming conventions.
Version: 1.0
Author: Stef Williams
Author URI: http://URI_Of_The_Plugin_Author
License: GPL2
*/
include ('cpt/sg_doc_schema.php'); 				//register doc_schema types
include ('cpt/sg_doc_schema_meta.php');			//add schema meta fields
include ('cpt/sg_doc.php');						//register doc type and redirect to post-new.php with query string based on schema
include ('cpt/sg_doc_meta.php');				//add meta fields based on schema definition
include ('cpt/save_meta.php');					//functions to save ALL custom metadata
include ('functions.php');						//all other plugin-related functions