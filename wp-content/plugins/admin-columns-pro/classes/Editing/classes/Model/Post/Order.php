<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Editing_Model_Post_Order extends ACP_Editing_Model {

	public function get_view_settings() {
		return array(
			'type' => 'textarea',
		);
	}

	public function save( $id, $value ) {
		$this->strategy->update( $id, array( 'menu_order' => $value ) );
	}

}
