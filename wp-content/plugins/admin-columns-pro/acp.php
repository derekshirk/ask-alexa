<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Admin Columns Pro plugin class
 *
 * @since 1.0
 */
final class ACP {

	/**
	 * License manager class instance
	 *
	 * @since 1.0
	 * @var ACP_License_Manager
	 */
	private $license_manager;

	/**
	 * Editing instance
	 *
	 * @since 4.0
	 * @var ACP_Editing_Addon
	 */
	private $editing;

	/**
	 * Filtering instance
	 *
	 * @since 4.0
	 * @var ACP_Filtering_Addon
	 */
	private $filtering;

	/**
	 * Sorting instance
	 *
	 * @since 4.0
	 * @var ACP_Sorting_Addon
	 */
	private $sorting;

	/**
	 * @var ACP_NetworkAdmin
	 */
	private $network_admin;

	/**
	 * @var null|string
	 */
	private $version = null;

	/**
	 * @since 3.8
	 */
	private static $_instance = null;

	/**
	 * @since 3.8
	 * @return ACP
	 */
	public static function instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * ACP constructor.
	 */
	private function __construct() {

		$this->init();

		// Hooks
		add_action( 'init', array( $this, 'localize' ) );

		add_filter( 'plugin_action_links', array( $this, 'add_settings_link' ), 1, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'add_settings_link' ), 1, 2 );
		add_action( 'ac/settings/after_menu', array( $this, 'display_beta_version_message' ) );

		add_filter( 'ac/show_banner', '__return_false' );
		add_action( 'wp_loaded', array( $this, 'init_license_manager' ) );

		add_action( 'ac/list_screen_groups', array( $this, 'register_list_screen_groups' ) );
		add_action( 'ac/list_screens', array( $this, 'register_list_screens' ) );

		add_action( 'ac/column_types', array( $this, 'register_columns' ) );
		add_filter( 'ac/plugin_column_class_name', array( $this, 'set_plugin_column_class_name' ) );
	}

	/**
	 * @since 4.0
	 */
	public function get_plugin_dir() {
		return plugin_dir_path( __FILE__ );
	}

	/**
	 * @since 4.0
	 */
	public function get_plugin_url() {
		return plugin_dir_url( __FILE__ );
	}

	/**
	 * Basename of the plugin, retrieved through plugin_basename function
	 *
	 * @since 1.0
	 * @var string
	 */
	public function get_basename() {
		return plugin_basename( ACP_FILE );
	}

	/**
	 * @since 4.0
	 */
	public function get_version() {
		if ( null === $this->version ) {
			$this->version = AC()->get_plugin_version( ACP_FILE );
		}

		return $this->version;
	}

	/**
	 * @since 4.0
	 */
	public function is_beta() {
		$version = $this->get_version();

		return false !== strpos( $version, 'beta' );
	}

	/**
	 * Handle localization
	 *
	 * @since 1.0.1
	 * @uses  load_plugin_textdomain()
	 */
	public function localize() {
		load_plugin_textdomain( 'codepress-admin-columns', false, dirname( $this->get_basename() ) . '/languages/' );
	}

	/**
	 * General plugin initialization, loading plugin module files
	 *
	 * @since 1.0
	 */
	public function init() {
		$classes_dir = $this->get_plugin_dir() . 'classes/';

		AC()->autoloader()->register_prefix( 'ACP_', $classes_dir );

		// api functions
		include_once $this->get_plugin_dir() . 'api.php';

		$this->editing = new ACP_Editing_Addon();
		$this->sorting = new ACP_Sorting_Addon();
		$this->filtering = new ACP_Filtering_Addon();
		$this->network_admin = new ACP_NetworkAdmin();

		new ACP_ExportImport();

		new ACP_ThirdParty_Addon();
		new ACP_LayoutScreen_Columns();
		new ACP_LayoutScreen_Table();
		new ACP_Table_ScreenOptions();
	}

	/**
	 * Init License Manager
	 */
	public function init_license_manager() {
		$this->license_manager = new ACP_License_Manager();

		if ( defined( 'ACP_LICENCE' ) ) {
			$this->license_manager->set_licence_key( ACP_LICENCE );
		}
	}

	/**
	 * @return ACP_License_Manager
	 */
	public function license_manager() {
		return $this->license_manager;
	}

	/**
	 * @since 4.0
	 */
	public function editing() {
		return $this->editing;
	}

	/**
	 * @since 4.0
	 */
	public function filtering() {
		return $this->filtering;
	}

	/**
	 * @since 4.0
	 */
	public function sorting() {
		return $this->sorting;
	}

	/**
	 * @since 4.0
	 */
	public function layouts( AC_ListScreen $listScreen ) {
		return new ACP_Layouts( $listScreen );
	}

	/**
	 * @since 4.0
	 */
	public function network_admin() {
		return $this->network_admin;
	}

	/**
	 * @since 1.0
	 * @see   filter:plugin_action_links
	 */
	public function add_settings_link( $links, $file ) {
		if ( $file === $this->get_basename() ) {
			$url = AC()->admin()->get_link( 'settings' );

			if ( is_network_admin() ) {
				$url = $this->network_admin->get_link();
			}

			array_unshift( $links, ac_helper()->html->link( $url, __( 'Settings' ) ) );
		}

		return $links;
	}

	/**
	 * @param string $dirname
	 *
	 * @return string Return directory folder where columns are located.
	 */
	private function get_columns_path( $dirname ) {
		return $this->get_plugin_dir() . 'classes/Column/' . $dirname;
	}

	/**
	 * @param AC_ListScreen $list_screen
	 */
	public function register_columns( AC_ListScreen $list_screen ) {

		$dirname = false;

		// Overwrite existing columns with Pro versions
		if ( $path = $list_screen->get_local_column_path() ) {
			$parts = explode( '/', $path );
			$dirname = end( $parts );
		}

		// Additional Pro Columns
		switch ( $list_screen->get_key() ) {
			case 'wp-ms_users' :
				$dirname = 'User';
				break;
			case 'wp-ms_sites' :
				$dirname = 'NetworkSite';
				break;
		}

		if ( 'taxonomy' === $list_screen->get_group() ) {
			$dirname = 'Taxonomy';
		}

		if ( $dirname ) {
			$list_screen->register_column_types_from_dir( $this->get_columns_path( $dirname ), 'ACP_' );
		}

		// Applies to ALL list screens

		$list_screen->register_column_type( new ACP_Column_CustomField );
		$list_screen->register_column_type( new ACP_Column_UsedByMenu );

		// Default Taxonomy columns
		$this->register_native_taxonomy_columns( $list_screen );

		/**
		 * Register column types
		 *
		 * @param AC_ListScreen $list_screen
		 */
		do_action( 'acp/column_types', $list_screen );
	}

	/**
	 * Register Taxonomy columns that are set by WordPress. These native columns are registered
	 * by setting 'show_admin_column' to 'true' as an argument in register_taxonomy();
	 *
	 * @see register_taxonomy
	 *
	 * @param AC_ListScreen $list_screen
	 */
	private function register_native_taxonomy_columns( AC_ListScreen $list_screen ) {
		foreach ( $list_screen->get_column_types() as $column ) {

			if ( false === strpos( $column->get_type(), 'taxonomy-' ) ) {
				continue;
			}

			$taxonomy = get_taxonomy( substr( $column->get_type(), 9 ) );

			if ( ! $taxonomy || ! $taxonomy->show_admin_column ) {
				continue;
			}

			$tax_column = new ACP_Column_NativeTaxonomy();
			$tax_column->set_type( $column->get_type() );

			$list_screen->register_column_type( $tax_column );
		}
	}

	/**
	 * @return string
	 */
	public function set_plugin_column_class_name() {
		return 'ACP_Column_Plugin';
	}

	/**
	 * @return string
	 */
	public function get_network_settings_url() {
		return $this->network_admin()->get_link();
	}

	/**
	 * Get a list of taxonomies supported by Admin Columns
	 *
	 * @since 1.0
	 *
	 * @return array List of taxonomies
	 */
	private function get_taxonomies() {
		$taxonomies = get_taxonomies( array( 'show_ui' => true ) );

		if ( isset( $taxonomies['post_format'] ) ) {
			unset( $taxonomies['post_format'] );
		}

		if ( isset( $taxonomies['link_category'] ) && ! get_option( 'link_manager_enabled' ) ) {
			unset( $taxonomies['link_category'] );
        }

		/**
		 * Filter the post types for which Admin Columns is active
		 *
		 * @since 2.0
		 *
		 * @param array $post_types List of active post type names
		 */
		return apply_filters( 'acp/taxonomies', $taxonomies );
	}

	/**
	 * @param AC_Groups $groups
	 */
	public function register_list_screen_groups( $groups ) {
		$groups->register_group( 'taxonomy', __( 'Taxonomy' ), 15 );
		$groups->register_group( 'network', __( 'Network' ), 5 );
	}

	/**
	 * @since 4.0
	 */
	public function register_list_screens() {

		if ( $taxonomies = $this->get_taxonomies() ) {
			foreach ( $taxonomies as $taxonomy ) {
				AC()->register_list_screen( new ACP_ListScreen_Taxonomy( $taxonomy ) );
			}
		}

		if ( is_multisite() ) {

			AC()->register_list_screen( new AC_ListScreen_User() );

			// Settings UI
			if ( AC()->admin_columns_screen()->is_current_screen() ) {

				// Main site
				if ( is_main_site() ) {
					AC()->register_list_screen( new ACP_ListScreen_MSUser() );
					AC()->register_list_screen( new ACP_ListScreen_MSSite() );
				}
			} // Table screen
			else {
				AC()->register_list_screen( new ACP_ListScreen_MSUser() );
				AC()->register_list_screen( new ACP_ListScreen_MSSite() );
			}
		}
	}

	/**
	 * Beta message
	 */
	public function display_beta_version_message() {
		if ( AC()->suppress_site_wide_notices() ) {
			return;
		}
		if ( ACP()->is_beta() ) : ?>
            <div class="notice notice-warning">
                <p>
					<?php printf( __( "You are using a beta version of %s.", 'codepress-admin-columns' ), 'Admin Columns Pro' ); ?>
                    <?php printf( __( "Please provide us with any feedback (bugs, UI or improvements) on the beta by creating a new topic on %s.", 'codepress-admin-columns' ), ac_helper()->html->link( ac_get_site_utm_url( 'forums/forum/beta-feedback/', 'beta-notice' ), __( 'our forum', 'codepress-admin-columns' ), array( 'target' => '_blank' ) ) ); ?>
                </p>
            </div>
			<?php
		endif;
	}

}

function ACP() {
	return ACP::instance();
}

// Backwards compatible
function acp_pro() {
	return ACP();
}

ACP();
