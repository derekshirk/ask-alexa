<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Filtering_DateTimePicker extends ACA_ACF_Filtering_DatePicker {

	protected function get_date_format() {
		return 'Y-m-d H:i:s';
	}

	/**
	 * @param array        $vars
	 * @param array|string $value
	 *
	 * @return mixed
	 */
	public function get_filtering_vars( $vars ) {
		$vars = $this->get_filtering_vars_date( $vars, array(
			'filter_format' => $this->get_filter_format(),
			'date_format'   => $this->get_date_format(),
			'type'          => 'date',
		) );

		return $vars;
	}

}
