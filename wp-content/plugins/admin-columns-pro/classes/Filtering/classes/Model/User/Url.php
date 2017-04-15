<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_User_Url extends ACP_Filtering_Model {

	public function filter_by_user_url( $query ) {
		global $wpdb;

		$value = $this->get_filter_value();
		$sql = $wpdb->prepare( ' = %s', $value );
		if ( 'cpac_empty' === $value ) {
			$sql = " LIKE ''";
		}
		if ( 'cpac_nonempty' === $value ) {
			$sql = " NOT LIKE ''";
		}

		$query->query_where .= " AND {$wpdb->users}.user_url" . $sql;
	}

	public function get_filtering_vars( $vars ) {
		add_action( 'pre_user_query', array( $this, 'filter_by_user_url' ) );

		return $vars;
	}

	public function get_filtering_data() {
		$data = array(
			'order' => false,
		);

		if ( $values = $this->strategy->get_values_by_db_field( 'user_url' ) ) {
			foreach ( $values as $value ) {
				$data['options'][ $value ] = $value;
			}
		}

		natsort( $data['options'] );

		$data['options']['cpac_disabled'] = '';
		$data['options']['cpac_empty'] = sprintf( __( "Without %s", 'codepress-admin-columns' ), $this->column->get_setting( 'label' )->get_value() );
		$data['options']['cpac_nonempty'] = sprintf( __( "Has %s", 'codepress-admin-columns' ), $this->column->get_setting( 'label' )->get_value() );

		return $data;
	}

}
