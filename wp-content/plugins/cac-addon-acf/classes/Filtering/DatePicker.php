<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Filtering_DatePicker extends ACA_ACF_Filtering {

	public function get_data_type() {
		return 'date';
	}

	/**
	 * @return string
	 */
	protected function get_date_format() {
		return 'Ymd';
	}

	public function register_settings() {
		$this->column->add_setting( new ACP_Filtering_Settings_Date( $this->column ) );
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
			'type'          => 'numeric',
		) );

		return $vars;
	}

	public function get_filtering_data() {
		$format = $this->get_filter_format();

		$options = $this->get_date_options_relative( $format );

		if ( ! $options ) {
			$options = $this->get_date_options( $this->get_meta_values(), $format, $this->get_date_format() );
		}

		return array(
			'empty_option' => true,
			'order'        => false,
			'options'      => $options,
		);
	}

	protected function get_filter_format() {
		$format = $this->column->get_setting( 'filter' )->get_value( 'filter_format' );

		if ( ! $format ) {
			$format = 'daily';
		}

		return $format;
	}

}
