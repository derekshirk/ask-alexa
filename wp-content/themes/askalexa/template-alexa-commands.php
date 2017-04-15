<?php /*
 * This file is part of the WP Theme and Portfolio of derek shirk
 * <https://derekshirk.com>
 * (c) derek shirk
 * [license] Don't be a jerk. Otherwise, learn, share, profit and so on. 

/* ---------------------------------------------------------- *
/* Template Name: Alexa Commands
/* ---------------------------------------------------------- */

$context = Timber::get_context();
$args = array( 
	'post_type' 			=> 'alexa-commands',
	'order' 				=> 'ASC',
	'orderby' 			=> 'rand',
	'posts_per_page' 	=> -1
) ;
$context['posts'] = Timber::get_posts($args) ;
$context['count'] = wp_count_posts();

// $love = get_post_meta( get_the_ID(), 'post_love', true );
// $love = ( empty( $love ) ) ? 0 : $love;

Timber::render( array( 'page-' . $post->post_name . '.twig', 'page.twig' ), $context );