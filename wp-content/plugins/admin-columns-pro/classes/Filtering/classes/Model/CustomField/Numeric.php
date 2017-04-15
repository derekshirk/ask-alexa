<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Model_CustomField_Numeric extends ACP_Filtering_Model_CustomField {

	public function __construct( $column ) {
		parent::__construct( $column );

		$this->set_data_type( 'numeric' );
	}

	public function get_data_type() {
		return 'numeric';
	}

	public function is_ranged() {
		return true;
	}

}
