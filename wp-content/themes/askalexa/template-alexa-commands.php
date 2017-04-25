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
$post = new TimberPost();
$context['post'] = $post;
$context['count'] = wp_count_posts();
$context['twitterShareText']      = "Fun Things to Ask Alexa " ;
$context['source']                        = "via https://derekshirk.com " ;
$context['socialSharePoster']   = "https://funthingstoaskalexa.com/wp-content/uploads/2017/04/fun-things-to-ask-alexa.png " ;
Timber::render( array( 'page-front.twig', 'page-' . $post->post_name . '.twig', 'page.twig' ), $context );