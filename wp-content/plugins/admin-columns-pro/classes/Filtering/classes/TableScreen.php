<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
class ACP_Filtering_TableScreen {

	public function __construct() {
		add_action( 'ac/table_scripts', array( $this, 'scripts' ) );
		add_action( 'ac/admin_head', array( $this, 'add_indicator' ) );
		add_action( 'ac/admin_head', array( $this, 'maybe_hide_default_dropdowns' ) );
		add_action( 'wp_ajax_acp_update_filtering_cache', array( $this, 'ajax_update_dropdown_cache' ) );
		add_action( 'ac/table/list_screen', array( $this, 'handle_filtering' ) ); // Before sorting
		add_action( 'ac/columns_stored', array( $this, 'clear_timeout' ) );
	}

	public function scripts() {
		wp_enqueue_style( 'acp-filtering-table', acp_filtering()->get_url() . 'assets/css/table' . AC()->minified() . '.css', array(), acp_filtering()->get_version() );
		wp_enqueue_script( 'acp-filtering-table', acp_filtering()->get_url() . 'assets/js/table' . AC()->minified() . '.js', array( 'jquery', 'jquery-ui-datepicker' ), acp_filtering()->get_version() );
	}

	/**
	 * Colors the column label orange on the listing screen when it is being filtered
	 *
	 * @param AC_ListScreen $list_screen
	 */
	public function add_indicator() {
		$class_names = array();

		$filtered_column_names = array_keys( $this->get_requested_filter_values() );

		foreach ( $filtered_column_names as $name ) {
			$class_names[] = 'thead tr th.column-' . $name;
			$class_names[] = 'thead tr th.column-' . $name . ' > a span:first-child';
		}

		if ( ! $class_names ) {
			return;
		}
		?>

        <style>
            <?php echo implode( ', ', $class_names ) .  '{ font-weight: bold; position: relative; }'; ?>
        </style>

		<?php
	}

	/**
	 * @since 3.8
	 *
	 * @param $list_screen AC_ListScreen
	 */
	public function maybe_hide_default_dropdowns( AC_ListScreen $list_screen ) {
		$disabled = array();

		foreach ( $list_screen->get_columns() as $column ) {
			$model = acp_filtering()->get_filtering_model( $column );

			if ( ! $model instanceof ACP_Filtering_Model_Delegated ) {
				continue;
			}

			if ( $model->is_active() ) {
				continue;
			}

			$disabled[] = '#' . $model->get_dropdown_attr_id();
		}

		if ( ! $disabled ) {
			return;
		}
		?>
        <style>
            <?php echo implode( ', ', $disabled ) . '{ display: none; }'; ?>
        </style>
		<?php
	}

	/**
	 * @since 3.6
	 */
	public function ajax_update_dropdown_cache() {
		check_ajax_referer( 'ac-ajax' );

		$list_screen = AC()->get_list_screen( filter_input( INPUT_POST, 'list_screen' ) );

		if ( ! $list_screen ) {
			wp_die();
		}

		$list_screen->set_layout_id( filter_input( INPUT_POST, 'layout' ) );
		$cache = $this->cache( 'timeout' . $list_screen->get_storage_key() );

		if ( $cache->time_left() && $this->is_cache_enabled() ) {
			wp_send_json_error( $cache->time_left() );
		}

		// 60 seconds cache
		$cache->set( true, 60 );

		wp_send_json_success( $this->get_html_dropdowns( $list_screen ) );
	}

	/**
	 * Init hooks for columns screen
	 *
	 * @since 1.0
	 */
	public function handle_filtering( AC_ListScreen $list_screen ) {

		// Display dropdown
		switch ( $list_screen->get_meta_type() ) {

			case 'post' :
				add_action( 'restrict_manage_posts', array( $this, 'filter_markup' ) );

				break;
			case 'user' :

				// Network Users
				if ( 'wp-ms_users' === $list_screen->get_key() ) {
					add_action( 'in_admin_footer', array( $this, 'filter_markup' ) );
					add_action( 'in_admin_footer', array( $this, 'filter_button' ) );
				} // Users
				else {
					add_action( 'restrict_manage_users', array( $this, 'filter_markup' ) );
					add_action( 'restrict_manage_users', array( $this, 'filter_button' ) );
				}

				break;
			case 'comment' :
				add_action( 'restrict_manage_comments', array( $this, 'filter_markup' ) );

				break;
		}

		// Handle filtering request
		foreach ( $list_screen->get_columns() as $column ) {

			if ( $model = acp_filtering()->get_filtering_model( $column ) ) {

				if ( false === $model->get_filter_value() ) {
					continue;
				}

				switch ( $list_screen->get_meta_type() ) {

					case 'post' :
						add_action( 'pre_get_posts', array( $model->get_strategy(), 'handle_filter_requests' ), 1 );

						break;
					case 'user' :
						add_action( 'pre_get_users', array( $model->get_strategy(), 'handle_filter_requests' ), 1 );

						break;
					case 'comment' :
						add_action( 'pre_get_comments', array( $model->get_strategy(), 'handle_filter_requests' ), 2 );

						break;
				}
			}
		}
	}

	/**
	 * @param AC_Column $column
	 *
	 * @return string
	 */
	public function get_requested_filter_value( AC_Column $column ) {
		$values = $this->get_requested_filter_values();

		if ( ! isset( $values[ $column->get_name() ] ) ) {
			return false;
		}

		return $values[ $column->get_name() ];
	}

	/**
	 * Get filter values from request
	 *
	 * @since 4.0
	 *
	 * @return array Filter values
	 */
	public function get_requested_filter_values() {
		$values = array();

		$list_screen = AC()->table_screen()->get_current_list_screen();

		// Single value
		$filters = $this->get_request_var();

		if ( ! empty( $filters ) ) {
			foreach ( $filters as $name => $value ) {
				if ( ! strlen( $value ) ) {
					continue;
				}

				$column = $list_screen->get_column_by_name( $name );

				if ( ! $column ) {
					continue;
				}

				$model = acp_filtering()->get_filtering_model( $column );

				if ( $model->is_active() ) {

					// Allow the usage <img> tags as a filter value
					$value = base64_decode( $value );

					$values[ $name ] = $value;
				}
			}
		}

		// Ranged values
		$filters_min = $this->get_request_var( 'min' );
		$filters_max = $this->get_request_var( 'max' );

		if ( $filters_min && $filters_max ) {
			foreach ( $filters_min as $name => $min ) {
				if ( ! strlen( $min ) ) {
					$min = false;
				}

				$max = isset( $filters_max[ $name ] ) && strlen( $filters_max[ $name ] ) ? $filters_max[ $name ] : false;

				if ( ! $min && ! $max ) {
					continue;
				}

				$column = $list_screen->get_column_by_name( $name );

				if ( ! $column ) {
					continue;
				}

				$model = acp_filtering()->get_filtering_model( $column );

				if ( $model->is_active() && $model->is_ranged() ) {
					$values[ $name ] = array(
						'min' => $min,
						'max' => $max,
					);
				}
			}
		}

		return $values;
	}

	/**
	 * Get a request var for all columns
	 *
	 * @param string $suffix
	 *
	 * @return array|false
	 */
	public function get_request_var( $suffix = '' ) {
		$key = 'acp_filter';

		if ( $suffix ) {
			$key .= '-' . ltrim( $suffix, '-' );
		}

		return filter_input( INPUT_GET, $key, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
	}

	/**
	 * Get an instance of cache
	 *
	 * @return ACP_Filtering_Cache
	 */
	public function cache( $name ) {
		return new ACP_Filtering_Cache( $name );
	}

	/**
	 * @since 4.0
	 *
	 * @param AC_ListScreen $list_screen
	 *
	 * @return string Filtering HTML dropdowns
	 */
	public function get_html_dropdowns( AC_ListScreen $list_screen ) {
		ob_start();

		foreach ( (array) $list_screen->get_columns() as $column ) {
			$model = acp_filtering()->get_filtering_model( $column );

			if ( ! $model ) {
				continue;
			}

			if ( $model instanceof ACP_Filtering_Model_Delegated ) {
				continue;
			}

			if ( ! $model->is_active() ) {
				continue;
			}

			$data = $model->get_filtering_data();

			if ( ! $data ) {
				continue;
			}

			if ( empty( $data['options'] ) && empty( $data['empty_option'] ) ) {
				continue;
			}

			// No HTML
			if ( ! empty( $data['options'] ) ) {
				foreach ( $data['options'] as $k => $v ) {
					$label = strip_tags( $v );

					if ( ! $label ) {
						$label = $k;
					}

					$data['options'][ $k ] = $label;
				}
			}

			$defaults = array(
				'empty_option' => false,
				'order'        => 'ASC',
				'options'      => array(),
			);

			// Merge with defaults
			$data = wp_parse_args( $data, $defaults );

			// Sort options
			if ( $data['order'] && $data['options'] ) {
				natcasesort( $data['options'] );

				if ( 'DESC' === $data['order'] ) {
					$data['options'] = array_reverse( $data['options'] );
				}
			}

			$this->display_dropdown( $column, $data );

			$this->cache( $list_screen->get_storage_key() . $column->get_name() )->set( $data );
		}

		return ob_get_clean();
	}

	/**
	 * @since 3.7
	 *
	 * @param               $columns AC_Column[] Columns
	 * @param AC_ListScreen $list_screen
	 */
	public function clear_timeout( $list_screen ) {
		$this->cache( 'timeout' . $list_screen->get_storage_key() )->delete();
	}

	/**
	 * @since 3.5
	 */
	public function filter_button() {
		?>
        <input type="submit" name="acp_filter_action" class="button" value="<?php echo esc_attr( __( 'Filter', 'codepress-admin-columns' ) ); ?>">
		<?php
	}

	/**
	 * Dropdown markup
	 * @since 3.6
	 *
	 * @param $column AC_Column
	 * @param $args   array
	 *
	 * @return void
	 */
	public function display_dropdown( AC_Column $column, $args = array() ) {
		$defaults = array(
			'options'      => array(),
			'empty_option' => false,
			'current_item' => false,
		);
		$args = wp_parse_args( $args, $defaults );

		$name = $column->get_name();
		$label = $column->get_setting( 'label' )->get_value();

		$setting = $column->get_setting( 'filter' );

		if ( ! $setting ) {
			return;
		}

		$top_label = $setting->get_value( 'filter_label' );

		if ( ! $top_label ) {
			$top_label = sprintf( __( 'All %s', 'codepress-admin-columns' ), trim( strip_tags( $label ) ) );
		}

		/**
		 * @since 4.0
		 */
		$args = apply_filters( 'acp/filtering/dropdown_args', $args, $column );

		$options = $args['options'];
		$empty_option = $args['empty_option'];

		if ( empty( $options ) && ! $empty_option ) {
			return;
		}

		// Limit max number of options to 10k. Prevent overloading the DOM and an unusable GUI.
		if ( count( $options ) > 10000 ) {
			return;
		}

		if ( $top_label ) {
			$options = array( '' => $top_label ) + $options;
		}

		if ( $empty_option ) {
			if ( count( $options ) > 1 ) {
				$options['cpac_disabled'] = true;
			}
			if ( true === $empty_option ) {
				$options['cpac_empty'] = __( 'Empty', 'codepress-admin-columns' );
				$options['cpac_nonempty'] = __( 'Not empty', 'codepress-admin-columns' );
			} else {
				$options['cpac_empty'] = $empty_option[0];
				$options['cpac_nonempty'] = $empty_option[1];
			}
		}

		$current_item = $args['current_item'];
		$sanitized = array();

		foreach ( $options as $value => $label ) {

			// Prevent slowing down the DOM with too large strings
			if ( strlen( $value ) > 6000 ) {
				continue;
			}

			if ( 'cpac_disabled' === $value ) {
				$label = '───────────';
			}

			// Crop label to 100 characters
			if ( strlen( str_replace( '&nbsp;', '', $label ) ) > 100 ) {
				$label = substr( $label, 0, 98 ) . '..';
			}

			// Allow the usage <img> tags as a filter value
			$value = base64_encode( $value );

			$sanitized[ $value ] = $label;
		}

		$id = "acp-filter-" . $name;

		?>
        <label for="<?php echo esc_attr( $id ); ?>" class="screen-reader-text"><?php echo sprintf( __( 'Filter by %s', 'codepress-admin-columns' ), $label ); ?></label>
        <select class="postform acp-filter<?php echo $current_item ? ' active' : ''; ?>" id="<?php echo esc_attr( $id ); ?>" name="acp_filter[<?php echo esc_attr( $name ); ?>]" data-current="<?php echo esc_attr( $current_item ); ?>">
			<?php foreach ( $sanitized as $value => $label ) : ?>
                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $current_item ); ?><?php echo 'cpac_disabled' === $value ? ' disabled' : ''; ?>>
					<?php echo esc_html( $label ); ?>
                </option>
			<?php endforeach; ?>
        </select>
		<?php
	}

	/**
	 * Display dropdown markup
	 */
	public function filter_markup() {

		// run once for users
		remove_action( 'restrict_manage_users', array( $this, 'filter_markup' ) );

		$list_screen = AC()->table_screen()->get_current_list_screen();

		foreach ( $list_screen->get_columns() as $column ) {

			$model = acp_filtering()->get_filtering_model( $column );

			if ( ! $model || ! $model->is_active() ) {
				continue;
			}

			// Range inputs or select dropdown
			if ( $model->is_ranged() ) {

				switch ( $model->get_data_type() ) {
					case 'date' :
						$this->display_range( array(
							'name'      => $column->get_name(),
							'label'     => $column->get_setting( 'label' )->get_value(),
							'type'      => 'date',
							'label_min' => __( 'Start date', 'codepress-admin-columns' ),
							'label_max' => __( 'End date', 'codepress-admin-columns' ),
						) );
						break;
					case 'numeric' :
						$this->display_range( array(
							'name'       => $column->get_name(),
							'label'      => $column->get_setting( 'label' )->get_value(),
							'type'       => 'number',
							'label_min'  => __( 'Min', 'codepress-admin-columns' ),
							'label_max'  => __( 'Max', 'codepress-admin-columns' ),
							'input_type' => 'number',
						) );
						break;
				}
			} else {
				$data = array();

				if ( $this->is_cache_enabled() ) {
					$data = $this->cache( $list_screen->get_storage_key() . $column->get_name() )->get();
				}

				// Placeholder text
				if ( empty( $data['options'] ) ) {
					$data['options'] = array( '' => __( 'Loading values ..', 'codepress-admin-columns' ) );
				}

				// Current selection
				$data['current_item'] = $this->get_request_var_column( $column->get_name() );

				$this->display_dropdown( $column, $data );
			}
		}

		do_action( 'acp/filtering/form', $list_screen );
	}

	/**
	 * @since 4.0
	 *
	 * @param array $args Range arguments.
	 */
	private function display_range( $args = array() ) {
		$defaults = array(
			'name'       => '',
			'label'      => '',
			'type'       => '',
			'label_min'  => '',
			'label_max'  => '',
			'input_type' => 'text',
		);

		$data = (object) wp_parse_args( $args, $defaults );

		$min = $this->get_request_var_column( $data->name, 'min' );
		$max = $this->get_request_var_column( $data->name, 'max' );

		$min_id = 'acp-filter-min-' . $data->name;
		$max_id = 'acp-filter-max-' . $data->name;
		?>

        <div class="acp-range <?php echo esc_attr( $data->type ); ?><?php echo ( $min || $max ) ? ' active' : ''; ?>">
            <div class="input_group">
                <label class="prepend" for="<?php echo esc_attr( $min_id ); ?>"><?php echo esc_html( $data->label ); ?></label>
                <input class="min<?php echo $min ? ' active' : ''; ?>" type="<?php echo esc_attr( $data->input_type ); ?>" placeholder="<?php echo esc_attr( $data->label_min ); ?>" name="acp_filter-min[<?php echo esc_attr( $data->name ); ?>]" value="<?php echo esc_attr( $min ); ?>" id="<?php echo esc_attr( $min_id ); ?>">
                <label class="append" for="<?php echo esc_attr( $max_id ); ?>"><?php _e( 'until', 'codepress-admin-columns' ); ?></label>
                <input class="max<?php echo $max ? ' active' : ''; ?>" type="<?php echo esc_attr( $data->input_type ); ?>" placeholder="<?php echo esc_attr( $data->label_max ); ?>" name="acp_filter-max[<?php echo esc_attr( $data->name ); ?>]" value="<?php echo esc_attr( $max ); ?>" id="<?php echo esc_attr( $max_id ); ?>">
            </div>
        </div>

		<?php
	}

	/**
	 * @return bool
	 */
	private function is_cache_enabled() {
		return apply_filters( 'acp/filtering/use_cache', true );
	}

	/**
	 * Get a request var for a single column
	 *
	 * @param string $column_name
	 * @param string $suffix
	 *
	 * @return false|string|int
	 */
	public function get_request_var_column( $column_name, $suffix = '' ) {
		$request_var = $this->get_request_var( $suffix );

		if ( ! $request_var || ! isset( $request_var[ $column_name ] ) ) {
			return false;
		}

		return $request_var[ $column_name ];
	}

}
