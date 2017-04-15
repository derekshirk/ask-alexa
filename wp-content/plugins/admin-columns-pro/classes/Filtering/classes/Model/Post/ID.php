<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_Post_ID extends ACP_Filtering_Model {

	public function __construct( $column ) {
		parent::__construct( $column );

		$this->set_data_type( 'numeric' );
	}

	public function is_ranged() {
		return true;
	}

	public function filter_by_id( $where ) {
		global $wpdb;

		$value = $this->get_filter_value();

		if ( $value['min'] ) {
			$where .= $where . $wpdb->prepare( "AND {$wpdb->posts}.ID >= %s", $value['min'] );
		}

		if ( $value['max'] ) {
			$where .= $wpdb->prepare( "AND {$wpdb->posts}.ID <= %s", $value['max'] );
		}

		return $where;
	}

	public function get_filtering_vars( $vars ) {
		if ( $this->is_ranged() ) {
			add_filter( 'posts_where', array( $this, 'filter_by_id' ) );
		}

		return $vars;
	}

	public function get_filtering_data() {
		return false;
	}

}
