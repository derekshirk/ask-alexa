<?php

if ( ! class_exists( 'Timber' ) ) {
	add_action( 'admin_notices', function() {
		echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php') ) . '</a></p></div>';
	});
	
	add_filter('template_include', function($template) {
		return get_stylesheet_directory() . '/static/no-timber.html';
	});
	
	return;
}

Timber::$dirname = array('templates', 'views');

class StarterSite extends TimberSite {

	function __construct() {
		// add_theme_support( 'post-formats' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );
		add_filter( 'timber_context', array( $this, 'add_to_context' ) );
		add_filter( 'get_twig', array( $this, 'add_to_twig' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		parent::__construct();
	}

	function register_post_types() {
		//this is where you can register custom post types
	}

	function register_taxonomies() {
		//this is where you can register custom taxonomies
	}

	function add_to_context( $context ) {
		// $context['foo'] = 'bar';
		// $context['stuff'] = 'I am a value set in your functions.php file';
		// $context['notes'] = 'These values are available everytime you call Timber::get_context();';
		$context['menu'] = new TimberMenu();
		$context['site'] = $this;
		return $context;
	}

	// function myfoo( $text ) {
	// 	$text .= ' bar!';
	// 	return $text;
	// }

	function add_to_twig( $twig ) {
		/* this is where you can add your own functions to twig */
		$twig->addExtension( new Twig_Extension_StringLoader() );
		$twig->addFilter('myfoo', new Twig_SimpleFilter('myfoo', array($this, 'myfoo')));
		return $twig;
	}

}

// should be called from within an init action hook
// add_shortcode('rating', 'mmplm_shortcode');

// Custom Post Types
include('admin/post-types/alexa-commands.php') ;

// WordPress Support
include('admin/hooks/wp-support.php') ;

// Body Class
include('admin/hooks/body-class.php') ;

// Post Like Ajax
// add_action('wp_ajax_nopriv_post-like', 'post_like');
// add_action('wp_ajax_post-like', 'post_like');

// function my_scripts_method() {
// 	wp_register_script('like_post', get_template_directory_uri().'/assets/scripts/post-like.js', array('jquery'), '', true );
// 	wp_enqueue_script( 'like_post' );
// }
// add_action( 'wp_enqueue_scripts', 'my_scripts_method' );


// wp_localize_script('like_post', 'ajax_var', array(
//     'url' => admin_url('admin-ajax.php'),
//     'nonce' => wp_create_nonce('ajax-nonce')
// ));




new StarterSite();
