<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Column_Comment extends ACA_ACF_Column {

	public function get_formatted_id( $id ) {
		return 'comment_' . $id;
	}

}
