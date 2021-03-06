<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Column_Taxonomy_Name extends AC_Column
	implements ACP_Column_EditingInterface {

	public function __construct() {
		$this->set_original( true );
		$this->set_type( 'name' );
	}

	public function editing() {
		return new ACP_Editing_Model_Taxonomy_Name( $this );
	}

}
