<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ACP_Column_Plugin
 *
 * This will load as the default Pro column for column added by plugins or themes
 *
 */
class ACP_Column_Plugin extends AC_Column_Plugin implements ACP_Column_EditingInterface {

	public function editing() {
		return new ACP_Editing_Model_Plugin( $this );
	}

}
