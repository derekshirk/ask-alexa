<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Filtering_Helper {

	/**
	 * @param int[] $post_ids Post ID's
	 *
	 * @return array
	 */
	public function get_post_titles( $post_ids ) {
		$titles = array();

		if ( $post_ids ) {
			foreach ( $post_ids as $id ) {
				$post = get_post( $id );

				if ( ! $post ) {
					continue;
				}

				$title = $post->post_title;

				if ( ! $title ) {
					$title = '#' . $post->ID;
				}

				// Add post name suffix to duplicate titles
				if ( in_array( $title, $titles ) ) {
					$suffix = $post->post_name;

					if ( ! $suffix ) {
						$suffix = $id;
					}

					$title .= ' (' . $suffix . ')';
				}

				$titles[ $id ] = $title;
			}
		}

		return $titles;
	}

	/**
	 * @param array $term_ids
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	public function get_term_names( $term_ids, $taxonomy ) {
		$values = array();

		if ( $term_ids ) {
			foreach ( $term_ids as $term_id ) {
				$term = get_term_by( 'id', $term_id, $taxonomy );

				if ( ! $term ) {
					continue;
				}

				$label = $term->name;

				if ( ! $label ) {
					$label = '#' . $term->term_id;
				}

				if ( in_array( $label, $values ) ) {
					$label .= ' (' . $term->slug . ')';
				}

				$values[ $term_id ] = $label;
			}
		}

		return $values;
	}

}
