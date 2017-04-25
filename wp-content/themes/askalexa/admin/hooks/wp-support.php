<?php 

/* ————————————————————————— */
/*  Remove wp-embed
/* ————————————————————————— */

add_action( 'wp_footer', 'my_deregister_scripts' );

function my_deregister_scripts(){
	wp_deregister_script( 'wp-embed' );
}


/* ————————————————————————— */
/*  Remove jQuery Migrate
/* ————————————————————————— */

add_filter( 'wp_default_scripts', 'dequeue_jquery_migrate' );

function dequeue_jquery_migrate(&$scripts){
	if(!is_admin()){
		$scripts->remove( 'jquery');
		// $scripts->add( 'jquery', false, array( 'jquery-core' ), '1.10.2' );
	}
}

/* ————————————————————————— */
/*  Add Favicon to wp-admin
/* ————————————————————————— */

function favicon(){
	echo '<link rel="shortcut icon" href="',get_template_directory_uri(),'/favicon.ico" />',"\n";
}
add_action('admin_head','favicon');

/* ---------------------------------------------------------- */
/* Enable SVG Uploading
/* ---------------------------------------------------------- */

function cc_mime_types($mimes) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');

/* ---------------------------------------------------------- */
/* Show SVG Preview in Media Uploader
/* ---------------------------------------------------------- */

// http://wordpress.stackexchange.com/questions/157480/ways-to-handle-svg-rendering-in-wordpress
function common_svg_media_thumbnails($response, $attachment, $meta){
	if( $response['type'] === 'image' && $response['subtype'] === 'svg+xml' && class_exists('SimpleXMLElement') ) {
		try {
			$path = get_attached_file($attachment->ID);
			if(@file_exists($path)) {
				$svg    = new SimpleXMLElement(@file_get_contents($path));
				$src    = $response['url'];
				$width    = (int) $svg['width'];
				$height   = (int) $svg['height'];

				//media gallery
				$response['image'] = compact( 'src', 'width', 'height' );
				$response['thumb'] = compact( 'src', 'width', 'height' );

				//media single
				$response['sizes']['full'] = array(
					'height'      => $height,
					'width'       => $width,
					'url'       => $src,
					'orientation'   => $height > $width ? 'portrait' : 'landscape',
				);
			}
		}
		catch(Exception $e){}
	}
	return $response;
}
add_filter('wp_prepare_attachment_for_js', 'common_svg_media_thumbnails', 10, 3);

/* ————————————————————————— */
/*  Disable WP Emjoi Support
/* ————————————————————————— */

function disable_wp_emojicons() {

	// all actions related to emojis
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );

	// filter to remove TinyMCE emojis
	add_filter( 'tiny_mce_plugins', 'disable_emojicons_tinymce' );
}
add_action( 'init', 'disable_wp_emojicons' );

// remove support from tinymce
function disable_emojicons_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}

// remove the DNS prefetch
add_filter( 'emoji_svg_url', '__return_false' );