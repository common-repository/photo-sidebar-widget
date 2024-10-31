<?php

// Photo Sidebar Widget
//
// Copyright (c) 2007-2008 Marcel Proulx
// http://www.district30.net/photo-sidebar-widget-version-20
//
// This is an add-on for WordPress
// http://wordpress.org/
//
// **********************************************************************
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// *****************************************************************

/*
Plugin Name: Photo Sidebar Widget
Plugin URI: http://www.district30.net/photo-sidebar-widget-version-20
Description: Integrate photos from an RSS feed via a sidebar widget.
Author: Marcel Proulx
Version: 2.2
Author URI: http://www.district30.net
*/ 

/* Function: disp_sm_feed
** This function does the actual display of the sidebar
**
** args: $args: environment variables (handled automatically by the hook)
** $widget_args: array or number indicating the instance to be displayed
** returns: nothing
*/
	
function disp_sm_feed( $args, $widget_args = 1 ) {
	extract( $args, EXTR_SKIP );
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$options = get_option('widget_photo_sb');
	if ( !isset($options[$number]) )
		return;
	
	extract($options[$number], EXTR_SKIP);
	$feed_url = explode(",",$feed_url);
	
	if ($clear_photo_cache) {
		if (empty_cache($cache_directory)) {
			$options[$number]['clear_photo_cache'] = ""; //make it blank
			update_option('widget_photo_sb',$options); //clear the clear_photo_cache flag
		}
	}

	echo $before_widget;
	echo $before_title
		. $title
		. $after_title;
	
	echo '<div id="photo_sb_wrapper-' . $number . '" align="center">';


	//Use WordPress' built-in RSS functionality
	if (!function_exists('MagpieRSS')) { // Check if another plugin is using RSS, may not work
		include_once (ABSPATH . WPINC . '/rss-functions.php');
		//error_reporting(E_ERROR);
	}


	foreach ($feed_url as $url) {
		//load the structured rss feed(s)
		$url = trim($url); //this will remove any extra "whitespace" (spaces, tabs, CR, LF, etc.) that may exist in the user-entered feed list
		$rss = fetch_rss($url);
		$channel = $rss->channel;
		$generator = $channel["generator"];
		$new_fetch = $rss->items;
		
		//print_r($new_fetch);
		
		if ($new_fetch) {
			foreach($new_fetch as $key=>$value) {
				$new_fetch[$key]["generator"] = $generator; //since our feeds may be mixed, each item needs to keep its generator info
			}


			//the first time through the loop, the array is set up automatically.  For all
			//subsequent trips through the loop (i.e. for additional feeds,) we have to
			//add to the array manually.  array_push($fetch,$old_fetch) just puts the whole
			//$old_fetch object as the last element of the array instead of adding all of the
			//individual elements, which is what we want.

			if (count($fetch) != 0) {
				foreach($new_fetch as $items) {
					array_push($fetch,$items);
				}
			}
			else $fetch=$new_fetch;
		}
	}

	if($fetch) {

		//sort the items (photos) randomly
		usort($fetch, rand_compare);

		$count = 0;
		foreach($fetch as $item) {
			//format the title correctly in case the photo title includes text that needs to be escaped
			$item["title"]=htmlspecialchars($item["title"]);

			//use the maximum number of photos to control the foreach loop
			//if we haven't displayed the maximum number of photos, build the HTML to
			//display an image including built-in (i.e inline) style elements to center the photo

			if ($count < $photo_count) {

				//get the image location based on the type of feed
				switch($item["generator"]) {
					case "http://www.smugmug.com/":
					case "Gallery 2 RSS Module, version 1.1.0":
					case "Blogger":
					case "pixelpost":
					case "Picasaweb":
					case "http://wordpress.org/?v=2.3.3":
						$image_tag = $item["description"];
						$item_url = extract_image_url($image_tag);
						break;
					case "http://www.flickr.com/":
						$image_tag = $item["description"];
						$item_url = extract_image_url($image_tag);
						$link_tag = $item["description"];
						$link_url = extract_link_url($link_tag);
						$item["link"] = $link_url; //the link provided by Flickr feeds doesn't always work
						break;
					case "Flickr": //Some Flickr feeds use the atom standard
						//echo $item["atom_content"] . "\n";
						$image_tag = $item["atom_content"];
						$item_url = extract_image_url($image_tag);
						break;
					default:
						if($item["id"])
							$item_url = $item["id"]; //this may be a SmugMug Atom feed
						else
							$item_url = "";
				}
					
				$item_url = cache_image($item_url, $use_cache, $cache_directory);

				if ($item_url == "") {
					echo "<br/>"; //include some whitespace for items for which the feed type isn't determined
					$count--; //decrement the counter so the user isn't shortchanged on images
				}
				else {
					$img_info = array(150,150); //set up a default in case getimagesize() isn't supported
					$img_info = @getimagesize($item_url);  //image width is in element 0 and height is element 1
					$img_width = $img_info[0];
					$img_height = $img_info[1];
					if ($item["generator"]=="http://www.smugmug.com/" && $img_width<=150) {
						//this is a standard (non-Gallery) SmugMug feed
						//don't rescale the images we get from the feed
						$img_scale = '';
					}
					else {
						//we'll treat "landscape" and "portrait" photos differently so that photos that are oriented
						//differently appear to be the same size
						if ($img_width < $img_height) {
							//this is probably a "portrait" image so we'll scale vertically to 150 pixels
							$img_scale = ' height="150"';
						}
						else {
							//otherwise, this is probably a "landscape" image so we'll scale horizontally to 150 pixels
							$img_scale = ' width="150"';
						}
					}
					//generate the appropriate code to show the photo
					echo '<a href="' . $item["link"] . '" title="' . $item["title"] . '"><img style = "padding: 2px 0px 2px 0px;" src="' . $item_url . '"';
					echo $img_scale;
					echo ' alt="' . $item["title"] . '" /></a>';
					echo "\n";

					if ($show_title) {
						//show the caption if requested
						if (strlen($item[title]) > 60) {
							$truncate_pos = strpos($item[title],' ',59); //find the first space after 50 characters
							if ($truncate_pos !== FALSE) //only truncate and add ellipsis if there is a space after 50 chars
								$item[title] = substr($item[title],0,$truncate_pos) . "..."; //cut off the end and add "..."
						}
						echo '<p>' . $item[title] . '</p>';
					}
				}
			}
			else break;

			$count = $count + 1;
		}
	}

	//finish sidebar code
	echo '</div>';

	echo $after_widget;
}

/* Function: extract_link_url
** this function extracts and returns the link from a description
** (or any other tag) returned by an RSS feed.
**
** args: RSS tag
** returns: image source if available; otherwise returns an empty string.
*/

function extract_link_url($tag) {
	if (strpos($tag, "href="))
		$start_pos = strrpos($tag , "href="); //we're looking for the second href link
	else
		return FALSE;
	if ($start_pos != 0) {
		$start_pos += 6; //we're looking for the part of the string after href="
		$end_pos = strpos($tag , "\"", $start_pos);
		if ($end_pos != 0)
			$link_url = substr($tag, $start_pos, $end_pos-$start_pos);
		else
			$link_url = ""; //there is no closing double-quote, so we'll ignore this element
	}
	else
		$link_url = "";  //it doesn't look like there is a link in this feed element
	return $link_url;
}

/* Function: extract_image_url
** this function extracts and return the image source URL from a description
** (or any other tag) returned by an RSS feed.
**
** args: RSS tag
** returns: image source if available; otherwise returns an empty string.
*/

function extract_image_url($tag) {
	$start_pos = strpos($tag , "src=");
	if ($start_pos != 0) {
		$start_pos += 5; //we're looking for the part of the string after src="
		$end_pos = strpos($tag , "\"", $start_pos);
		if ($end_pos != 0)
			$image_url = substr($tag, $start_pos, $end_pos-$start_pos);
		else
			$image_url = ""; //there is no closing double-quote, so we'll ignore this element
	}
	else
		$image_url = "";  //it doesn't look like there is a photo in this feed element
	return $image_url;
}

/* Function: cache_image
** this function checks for a valid cache directory and a cached image matching
** the image called.  If a cached image exists, cache_image returns its URI;
** otherwise it caches the image and returns the new file's URI.  If caching is
** disabled or if the given path is invalid (not writable, etc), cache_image returns
** the remote file URI instead.
** args: remote image URI, use cache flag, cache directory
** returns: URI
*/

function cache_image($image_url, $use_cache, $cache_dir) {

	if($use_cache and is_writable($cache_dir)) {
		$image_file = $cache_dir . substr($image_url,strrpos($image_url,"/")+1);

		# check if file already exists in cache
		# if not, grab a copy of it
		if (!file_exists($image_file)) {
			if ( function_exists('curl_init') ) { // check for CURL, if not use fopen
				$curl = curl_init();
				$local_image = fopen($image_file, "wb");
				curl_setopt($curl, CURLOPT_URL, $image_url);
				curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
				curl_setopt($curl, CURLOPT_FILE, $local_image);
				curl_exec($curl);
				curl_close($curl);
			}
			else {
				$file_data = "";
				$remote_image = fopen($image_url, 'rb');
				if ($remote_image) {
					while(!feof($remote_image)) {
						$file_data.= fread($remote_image,1024*8);
					}
				}
				fclose($remote_image);
				$local_image = fopen("$image_file", 'wb');
				fwrite($local_image,$file_data);
				fclose($local_image);
			} // end CURL check
		} // end file check
		$path = get_bloginfo('wpurl') . "/";
		return $path . $image_file;
	}
	else
		return $image_url;
}

/* Function: empty_cache
** this function checks for a valid cache directory and clears
** any JPG files from it.
**
** args: cache directory
** returns: nothing
*/

function empty_cache($cache_dir) {

	if (is_writable($cache_dir)) {
		$dirhandle = opendir($cache_dir);
		while (($filename = readdir($dirhandle))!== FALSE) {
			if ($filename != '.' and $filename != '..' and is_writable($cache_dir.$filename)) {
				$file_extension = strtolower(substr($filename,strrpos($filename,'.')));
				if ($file_extension == ".jpg")
					unlink($cache_dir.$filename); //delete the file
			}
		}
		return TRUE;
	}
	else
		return FALSE;
}

/* Function: rand_compare
** To be used with an array sort function (i.e. usort), this function 
** will randomly sort an array
**
** args: $x=string; $y=string (handled by the function call)
** returns: a random integer between -1 and 1
*/

function rand_compare($xt, $yt) {
	// We don't care about the array elements passed on so we'll
	// create a random value for the return value
	return mt_rand(-1,1);
}

/* Function: photo_sb_control
** 
** This function draws the controls form on the widget page and 
** saves the settings when the "Save" button is clicked
**
** args: $widget_args-array or int containing the instance number being controlled
** returns: nothing
*/

function photo_sb_control($widget_args) {
	global $wp_registered_widgets;
	static $updated = false; // Whether or not we have already updated the data after a POST submit

	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$options = get_option('widget_photo_sb');
	if ( !is_array($options) )
		$options = array();

	// We need to update the data
	if ( !$updated && !empty($_POST['sidebar']) ) {
		// Tells us what sidebar to put the data in
		$sidebar = (string) $_POST['sidebar'];

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		foreach ( $this_sidebar as $_widget_id ) {
			// Remove all widgets of this type from the sidebar.  We'll add the new data in a second.  This makes sure we don't get any duplicate data
			// since widget ids aren't necessarily persistent across multiple updates
			if ( 'disp_sm_feed' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				if ( !in_array( "photo_sb-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed. "many-$widget_number" is "{id_base}-{widget_number}
					unset($options[$widget_number]);
			}
		}

		foreach ( (array) $_POST['photo_sb'] as $widget_number => $widget_photo_sb_instance ) {
			// compile data from $widget_random_image_instance
			$title = strip_tags(stripslashes( $widget_photo_sb_instance['title']));
			$feed_url=strip_tags(stripslashes( $widget_photo_sb_instance['feed_url']));
			$photo_count=strip_tags(stripslashes( $widget_photo_sb_instance['photo_count']));
			$show_title=strip_tags(stripslashes( $widget_photo_sb_instance['show_title']));
			$use_cache=strip_tags(stripslashes( $widget_photo_sb_instance['use_cache']));
			$clear_photo_cache=strip_tags(stripslashes( $widget_photo_sb_instance['clear_photo_cache']));
			$cache_directory=strip_tags(stripslashes( $widget_photo_sb_instance['cache_directory']));
			
			//$options[$widget_number] = array( 'title' => $title );  // Even simple widgets should store stuff in array, rather than in scalar
		
			$options[$widget_number] = compact('title', 'feed_url', 'photo_count', 'show_title', 'use_cache', 'clear_photo_cache', 'cache_directory');
		}
		
		update_option('widget_photo_sb', $options);

		$updated = true; // So that we don't go through this more than once
	}


	// Here we echo out the form
	if ( -1 == $number ) { // We echo out a template for a form which can be converted to a specific form later via JS
		$title = 'Photos';
		$feed_url = '';
		$photo_count = '5';
		$show_title = FALSE;
		$use_cache = TRUE;
		$clear_photo_cache = FALSE;
		$cache_directory = 'wp-content/plugins/photo-sb/';
		$number = '%i%';
	} 
	else {
		$title = attribute_escape($options[$number]['title']);
		$feed_url=$options[$number]['feed_url'];
		$photo_count=attribute_escape($options[$number]['photo_count']);
		$show_title=attribute_escape($options[$number]['show_title']);
		$use_cache=attribute_escape($options[$number]['use_cache']);
		$clear_photo_cache=attribute_escape($options[$number]['clear_photo_cache']);
		$cache_directory=$options[$number]['cache_directory'];
	}

	// The form has inputs with names like widget-many[$number][something] so that all data for that instance of
	// the widget are stored in one $_POST variable: $_POST['widget-many'][$number]
?>
	<p style="text-align:left; line-height=1.2em"><label for="photo_sb-title-<?php echo $number; ?>">Title:  <input style="width: 200px;" id="photo_sb-title-<?php echo $number; ?>" name="photo_sb[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" /></label></p>
	<p style="text-align:left; line-height=1.2em"><label for="photo_sb-feed_url-<?php echo $number; ?>">RSS Feed URL's (seperate URL's with ", "):  <br/><textarea rows="3" cols="50" id="photo_sb-feed_url-<?php echo $number; ?>" name="photo_sb[<?php echo $number; ?>][feed_url]"><?php echo $feed_url; ?></textarea></label></p>
	<p style="text-align:left; line-height=1.2em"><label for="photo_sb-photo_count-<?php echo $number; ?>">Number of photos to show: </label><select name="photo_sb[<?php echo $number; ?>][photo_count]" id="photo_sb-photo_count-<?php echo $number; ?>" style="width: 50px;">
<?php
	//create an option box with a set number of values generated by a for loop
	for($list_val=1; $list_val <= 20; $list_val ++) {
		if ($list_val == $photo_count)  
			echo '<option selected>' . $list_val . '</option>';
		else
			echo '<option>' . $list_val . '</option>';
	}
?>
	</select></p>
	<p style="text-align:left; line-height=1.2em"><label for="photo_sb-show_title-<?php echo $number; ?>">Show caption beneath photo?</label><input style="margin-left: 10;" id="photo_sb-show_title-<?php echo $number; ?>" name="photo_sb[<?php echo $number; ?>][show_title]" type="checkbox" value="1" <?php echo  $show_title ? 'checked' : ''; ?>></input></p>
	<p style="text-align:left; line-height=1.2em"><label for="photo_sb-use_cache-<?php echo $number; ?>">Use image cacheing?</label><input style="margin-left: 10;" id="photo_sb-use_cache-<?php echo $number; ?>" name="photo_sb[<?php echo $number; ?>][use_cache]" type="checkbox" value="1" <?php echo  $use_cache ? 'checked' : ''; ?>></input>
	<label for="photo_sb-clear_photo_cache-<?php echo $number; ?>">Clear image cache (occurs on next page load)?</label><input style="margin-left: 10;" id="photo_sb-clear_photo_cache-<?php echo $number; ?>" name="photo_sb[<?php echo $number; ?>][clear_photo_cache]" type="checkbox" value="1" <?php echo  $clear_photo_cache ? 'checked' : ''; ?>></input></p>
	<p style="text-align:left; line-height=1.2em"><label for="photo_sb-cache_directory-<?php echo $number; ?>">Cache Directory:  <input style="width: 400px;" id="photo_sb-cache_directory-<?php echo $number; ?>" name="photo_sb[<?php echo $number; ?>][cache_directory]" type="text" value="<?php echo $cache_directory; ?>" /></label></p>
	<input type="hidden" id="photo_sb-submit-<?php echo $number; ?>" name="photo_sb-submit-<?php echo $number; ?>" value="1" />
	
<?php
}

/* Function: photo_sb_register
** 
** Registers the random_image widgets with the widget page
**
** args: none
** returns: nothing
*/

function photo_sb_register() {
	if ( !$options = get_option('widget_photo_sb') )
		$options = array();
		
	$widget_ops = array('classname' => 'widget_many', 'description' => __('Displays random images from RSS photo feeds'));
	$control_ops = array('width' => 600, 'height' => 315, 'id_base' => 'photo_sb');
	$name = __('RSS Photos');

	$registered = false;
	foreach ( array_keys($options) as $o ) {
		// Old widgets can have null values for some reason
		if ( !isset($options[$o]['title']) )
			continue;

		// $id should look like {$id_base}-{$o}
		$id = "photo_sb-$o"; // Never never never translate an id
		$registered = true;
		wp_register_sidebar_widget( $id, $name, 'disp_sm_feed', $widget_ops, array( 'number' => $o ) );
		wp_register_widget_control( $id, $name, 'photo_sb_control', $control_ops, array( 'number' => $o ) );
	}

	// If there are none, we register the widget's existance with a generic template
	if ( !$registered ) {
		wp_register_sidebar_widget( 'photo_sb-1', $name, 'disp_sm_feed', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'photo_sb-1', $name, 'photo_sb_control', $control_ops, array( 'number' => -1 ) );
	}
}

// This is important
add_action( 'widgets_init', 'photo_sb_register' )


?>