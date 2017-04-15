<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Only used for 3rd party column.
 */
class ACP_Editing_Model_Plugin extends ACP_Editing_Model {

	public function get_ajax_options( $request ) {

		/**
		 * @since 4.0
		 *
		 * @param array $options
		 * @param string $search
		 * @param string $page
		 * @param ACP_Editing_Model_Plugin $this
		 */
		return apply_filters( 'acp/editing/ajax_options', parent::get_ajax_options( $request ), $request, $this );
	}

	public function register_settings() {

		/**
		 * @since 4.0
		 *
		 * @param bool false
		 * @param ACP_Editing_Model_Plugin $this
		 */
		$is_active = apply_filters( 'acp/editing/active', false, $this );
		$is_active = apply_filters( 'acp/editing/active/' . $this->column->get_type(), $is_active, $this );

		if ( $is_active ) {
			parent::register_settings();
		}
	}

}
