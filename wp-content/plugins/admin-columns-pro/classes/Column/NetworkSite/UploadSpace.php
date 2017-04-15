<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Column_NetworkSite_UploadSpace extends AC_Column {

	public function __construct() {
		$this->set_type( 'column-msite_uploadspace' );
		$this->set_label( __( 'Upload Space', 'codepress-admin-columns' ) );
	}

	public function get_value( $id ) {
		switch_to_blog( $id );

		$allowed = get_space_allowed();

		if ( $allowed < 0 ) {
			$allowed = 0;
		}

		$allowed = $allowed * MB_IN_BYTES;
		$used = get_space_used() * MB_IN_BYTES;

		$total = $allowed;
		if ( $this->no_upload_restrictions() ) {
			$total = -1; // Infinitive
		}

		restore_current_blog();

		$args = array(
			'current'     => $used,
			'total'       => $total,
			'label_left'  => ac_helper()->file->get_readable_filesize( $used, 1, '-' ),
			'label_right' => ac_helper()->file->get_readable_filesize( $allowed, 1, '0 MB' )
		);

		return ac_helper()->html->progress_bar( $args );
	}

	private function no_upload_restrictions() {
		return '1' === get_site_option( 'upload_space_check_disabled' );
	}

}
