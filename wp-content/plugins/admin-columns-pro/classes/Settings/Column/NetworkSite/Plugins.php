<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Settings_Column_NetworkSite_Plugins extends AC_Settings_Column
	implements AC_Settings_FormatValueInterface {

	private $plugin_display;

	protected function define_options() {
		return array( 'plugin_display' );
	}

	public function create_view() {

		$options = array(
			'count' => __( 'Count', 'codepress-admin-columns' ),
			'list'  => __( 'List', 'codepress-admin-columns' ),
		);

		$view = new AC_View( array(
			'label'   => __( 'Display Format', 'codepress-admin-columns' ),
			'setting' => $this->create_element( 'select' )->set_options( $options ),
		) );

		return $view;
	}

	/**
	 * @return string
	 */
	public function get_plugin_display() {
		return $this->plugin_display;
	}

	/**
	 * @param string $plugin_display
	 *
	 * @return bool
	 */
	public function set_plugin_display( $plugin_display ) {
		$this->plugin_display = $plugin_display;

		return true;
	}

	public function format( $plugins, $blog_id ) {
		if ( empty( $plugins ) ) {
			return ac_helper()->string->get_empty_char();
		}

		natcasesort( $plugins );

		// Add link
		if ( current_user_can( 'activate_plugins') ) {

			foreach ( $plugins as  $k => $plugin ) {
				$plugins[ $k ] = ac_helper()->html->link( get_admin_url( $blog_id, 'plugins.php' ), $plugin );
			}
		}

		switch ( $this->get_plugin_display() ) {
			case 'list' :
				$plugins = implode( "<br/>", $plugins );

				break;
			default :
				$plugins = ac_helper()->html->tooltip( count( $plugins ), implode( '<br/>', $plugins ) );
		}

		return $plugins;
	}

}
