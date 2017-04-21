<?php /*
 * This file is part of the WP Theme and Portfolio of derek shirk
 * <https://derekshirk.com>
 * (c) derek shirk
 * [license] Don't be a jerk. Otherwise, learn, share, profit and so on. 

/* ---------------------------------------------------------- *
/* Template Name: Submission Success
/* ---------------------------------------------------------- */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;
$context['twitterShareText']      = "Check out this post" ;
$context['source']                        = "via derekshirk.com" ;

Timber::render( array( 'page-' . $post->post_name . '.twig', 'page.twig' ), $context );