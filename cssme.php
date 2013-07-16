<?php
/*
Plugin Name: CSS Me
Plugin URI: http://www.micahblu.com/
Description: Add Custom CSS to any single page or post
Version: 1.0
Author: Micah Blu
Author URI: http://www.micahblu.com
License: GPL2
Copyright 2013 Micah Blu Sylvester
*/


/* Define the custom box */

add_action( 'add_meta_boxes', 'css_me_add_custom_box' );

// backwards compatible (before WP 3.0)
add_action( 'admin_init', 'css_me_add_custom_box', 1 );

/* Do something with the data entered */
add_action( 'save_post', 'css_me_save_postdata' );

/* Adds a box to the main column on the Post and Page edit screens */
function css_me_add_custom_box() {
    $screens = array( 'post', 'page' );
    foreach ($screens as $screen) {
        add_meta_box(
            'css-me',
            __( 'Add Custom CSS', 'cssme' ),
            'css_me_inner_custom_box',
            $screen
        );
    }
}

/* Prints the box content */
function css_me_inner_custom_box( $post ) {

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'css_me_noncename' );

  // The actual fields for data entry
  // Use get_post_meta to retrieve an existing value from the database and use the value for the form
  $value = get_post_meta( $post->ID, 'css_me', true );
 
  echo '<textarea id="css-me" name="css_me_css" style="width:100%; box-sizing: border-box; min-height: 250px">'.esc_attr($value).'</textarea>';
}

/* When the post is saved, saves our custom data */
function css_me_save_postdata( $post_id ) {

  // First we need to check if the current user is authorised to do this action. 
  if ( 'page' == $_POST['post_type'] ) {
    if ( ! current_user_can( 'edit_page', $post_id ) )
        return;
  } else {
    if ( ! current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // Secondly we need to check if the user intended to change this value.
  if ( ! isset( $_POST['css_me_noncename'] ) || ! wp_verify_nonce( $_POST['css_me_noncename'], plugin_basename( __FILE__ ) ) )
      return;

  // Thirdly we can save the value to the database

  //if saving in a custom table, get post_ID
  $post_ID = $_POST['post_ID'];
  //sanitize user input
  $mydata = $_POST['css_me_css'];

  // Do something with $mydata 
  // either using 
  add_post_meta($post_ID, 'css_me', $mydata, true) or
  update_post_meta($post_ID, 'css_me', $mydata);
  // or a custom table (see Further Reading section below)
}


add_action("wp_head", "css_me_insert_custom_css");

function css_me_insert_custom_css(){
	global $post;
	
	$custom_css = get_post_meta($post->ID, 'css_me');
	
	if( is_single() || is_page() ) :
  ?>
  <!-- AnyPage CSS -->
  <style type="text/css">
  <?php echo $custom_css[0]; ?>
  </style>
<?php endif;

} ?>
