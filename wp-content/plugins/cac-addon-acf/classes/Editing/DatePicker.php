<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Editing_DatePicker extends ACA_ACF_Editing_Options {

	public function is_editable() {
		$return = true;

		// For ACF4 we have to check the date_format because we only support yymmdd
		if ( $date_format = $this->column->get_field()->get( 'date_format' ) ) {
			$return = ( 'yymmdd' == $date_format );
		}

		return $return;
	}

	public function get_view_settings() {
		$data = parent::get_view_settings();

		$data['type'] = 'date';

		return $data;
	}

}
