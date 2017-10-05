<?php 
	
	/*
		Plugin Name: WP All Import - SSI Client Add On
		Plugin URI: https://github.com/Hube2/wpai-ssi-client-add-on
		Description: Site-Seeker, Inc. WP All Import Add On
		Version: 0.0.1
		Author: John Huebner for Site-Seeker, Inc.
	*/
	
	/*
		This file is intended as a starting place for creation of add ons for client sites
		it is a class that contains all of the necessary hooks and function for modification
		of WP All Import that might be necessary for a specific client site. This plugin should
		be mofified as needed for each client web site. This file also contains some genereic
		functions that can be used as helpers for imports
	*/
	
	if (!defined('WPINC')) {die;}
	
	/*
		for more information on import hooks, ect
		see 
		http://www.wpallimport.com/documentation/advanced/action-reference/
		http://www.wpallimport.com/documentation/advanced/execute-php/
	*/
	
	/*
		Functions at the top of this file are used to run functions
		on import values see the second link above Here is the example given there
		
		This is added to the import field
		[my_custom_function({my_element[1]})]
		
		and this is the function
		function my_custom_function($x) {
			// do something to $x
			return $x;
		}
		
		This can also be used to call any standard php or WP function
		example
		[str_replace(",", "", {your_price_element[1]})]
		
	*/
	
	function image_import_loc($value) {
		/*
			The purpost of this function is so that an FTP location can be supplied to
			the client where they can upload files to that will be imported. This function
			can be called during the import process to alter import values from just a
			file name to a full URL on the current hosing site in order to allow the
			files to be imported from that location.
			
			example of calling this function from import template
			[image_import_loc({file_field_node_name[1]})]
		*/
		
		// folder name of the root of this site where files will be stored
		$folder_name = '__files_for_import';
		
		global $wpdb;
		$value = trim($value);
		$value = trim($value, ',');
		$value = preg_replace('/\s+/s', '', $value);
		if (!$value) {
			return $value;
		}
		$values = explode(',', $value);
		$new_value = array();
		foreach ($values as $index => $value) {
			$value = trim($value);
			if (trim($value) == '') {
				// no value, preserve count of values
				$new_value[] = $value;
				continue;
			}
			if (preg_match('#^(https?:)?//#is', $value)) {
				// alreadh contains domain part, keep it
				$new_value[] = $value;
				continue;
			}
			
			$location = 'http';
			if (is_ssl()) {
				$location .= 's';
			}
			$location .= '://'.$_SERVER['HTTP_HOST'].'/'.$folder_name.'/'.$value;
			$new_value[] = $location;
			
		}
		$value = implode(',', $new_value);
		return $value;
	} // end function image_import_loc
	
	new ssi_wpai_client_add_on();
	
	class ssi_wpai_client_add_on {
		
		public function __construct() {
			add_action('init', array($this, 'init'), 20);
			add_action('pmxi_before_xml_import', array($this, 'before_import'), 10, 1);
			add_filter('wp_all_import_is_post_to_update', array($this, 'is_post_to_update'), 10, 3);
			add_filter('wp_all_import_is_post_to_create', array($this, 'is_post_to_create'), 10, 2);
			add_action('pmxi_update_post_meta', array($this, 'update_post_meta'), 10, 3);
			add_action('pmxi_attachment_uploaded',  array($this, 'attachment_uploaded'), 10, 3);
			add_action('pmxi_gallery_image', array($this, 'update_attachment'), 10, 3);
			add_action('pmxi_saved_post', array($this, 'post_saved'), 10, 1);
			add_action('pmxi_after_xml_import', array($this, 'after_import'), 10, 1);
		} // end public function __construct
		
		public function init() {
			// hook 'init'
			// runs in init to do any setup that might be required
		} // end public function init
		
		public function before_import($import_id) {
			// hook pmxi_before_xml_import
			/*
				This hook is called before WP All Import starts the import process. 
				It is generally used if you are performing cron based imports and 
				you need to run some code before WP All Import starts the import process.
			*/
			
		} // end public function before_import
		
		public function is_post_to_create($nodes, $import_id) {
			// hook wp_all_import_is_post_to_create
			/*
				undocumented wp all import hook it works the same as is_post_to_update
				this filter gets a list of the nodes, columns of data to be imported
				and the import ID. This can be used to check to see if the data is valid
				if not valid you can return false to abort the insertion of the new post
			*/
			$valid = true;
			
			return $valid;
		} // end public function is_post_to_create
		
		public function is_post_to_update($post_id, $nodes, $import_id) {
			// hook wp_all_import_is_post_to_update
			/*
				This filter can be called to determine if a post should be updated or skipped. 
				It takes the post ID and, optionally, an array of XML nodes. The returned 
				value should be either true to update the post or false to skip it.
				
				*** IMPORTANT NOTE ***
				the people that built the import plugin do not know how to use filters properly.
				They don't send the default value when calling the filter, only the other parameters.
				This filter must return either true or false and cannot simply return the default
				value as with a correctly constructed filter.
				
				I need to investigate what is in nodes and exaclty when this is called
				since this will be the primary function that will determine if input is
				valid and skip any invalid rows I'm going to have to test the import and write these values to a
				file so I can see what's in the arguments
				
			*/
			
			$valid = true;
			
			return $valid;
		} // end public function is_post_to_update
		
		public function update_post_meta($post_id, $meta_key, $meta_value) {
			// hook pmxi_update_post_meta
			/*
				This hook is called after WP All Import creates/updates a post meta.
			*/
		} // end public function update_post_meta
		
		public function attachment_uploaded($post_id, $attachment_id, $file_path) {
			// hook pmxi_attachment_uploaded
			/*
				This hook is called after WP All Import creates/updates a post attachment file
			*/
		} // end public function attachment_uploaded
		
		public function update_attachment($post_id, $attachment_id, $file_path) {
			//hook pmxi_gallery_image
			/*
				This hook is called after WP All Import creates/updates a post attachment image
			*/
		} // end public function update_attachment
		
		public function post_saved($post_id) {
			// hook pmxi_saved_post
			/*
				This hook is called after WP All Import creates or updates a post.
				It is generally used if you need to perform further actions on the 
				imported data, like serialize it, use an API for geocoding coordinates, 
				use it for some other purpose like comment generation, etc
			*/
			if (function_exists('FWP')) {
				// if facetwp is installed, do indexing
				FWP()->indexer->index($post_id);
			}
			
			if (class_exists('acf')) {
				$this->acf_update_fields($post_id);
			}
			
		} // end public function post_saved
		
		public function after_import($import_id) {
			// hook pmxi_after_xml_import
			/*
				This hook is called after WP All Import finishes an import. 
				It is generally used if you are performing cron based imports and 
				you need to do some clean up after the import is complete.
			*/
			
			if (function_exists('FWP')) {
				// if facet wp is installed, run the indexer on this page
				FWP()->indexer->index();
			}
			
		} // end public function after_import
		
		private function acf_update_fields($post_id) {
			// this function will update any checkbox, radio or select fields imported
			// to new choice values to these fields when new values are present in the import
			
			
			
		} // end private function acf_update_fields
		
	} // end class ssi_wpai_client_add_on
	
?>