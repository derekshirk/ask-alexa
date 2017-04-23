<?php /*
 * This file is part of the funthingstoaskalexa.com
 * <http://funthingstoaskalexa.com>
 * (c) derekshirk <derekshirk@gmail.com>
 * [license] Please view the License file that is included with this source code.
 */

 /* ---------------------------------------------------------- */
 /*   Alexa Commands Post Type
 /* ---------------------------------------------------------- */

function ds_create_cpt_alexa_commands() {

	$name 		= "Alexa Commands" ;
	$singular 	= "Alexa Command" ;
	$simple 	= "Commands" ;
	$plural 		= $name ;

	$labels = array(
		'name' 					=> __( $name ),
		'menu_name' 			=> __( $simple ),
		'singular_name' 		=> __( $singular ),
		'edit_item' 				=> __( 'Edit ' . $singular ),
		'add_new' 				=> _x( 'Add New ',  $singular ),
		'add_new_item' 		=> __( 'Add New ' . $singular ),
		'new_item' 				=> __( 'New ' . $singular ),
		'view_item' 				=> __( 'View ' . $singular ),
		'search_items' 			=> __( 'Search ' . $name ),
		'not_found' 				=> __( 'No ' . $name .'Found' ),
		'not_found_in_trash' 	=> __( 'No ' .$name. 'Found in Trash' ),
		'parent_item_colon' 	=> ''
	) ;

	$args = array(
		'labels' 					=> $labels,
		'menu_icon' 			=> 'dashicons-microphone',
		'public' 					=> true,
		'publicly_queryable' 	=> true,
		'show_ui' 				=> true,
		'query_var' 				=> true,
		'hierarchical' 			=> true,
		'has_archive' 			=> false,
		'show_in_menu' 		=> true,
		'taxonomies' 			=> array('post_tag'),
		'rewrite' 				=> array(
									'slug' 	=> 'ask-alexa'
								),
		'supports' 				=> array( 'title', 'excerpt', 'editor', 'thumbnail', 'custom-fields' )
	);
	register_post_type( 'alexa-commands', $args );
}

add_action( 'init', 'ds_create_cpt_alexa_commands' );


 /* ---------------------------------------------------------- */
 /*   Hook-up Custom Taxonomies
 /* ---------------------------------------------------------- */

// require 'terms/portfolio-terms.php';
