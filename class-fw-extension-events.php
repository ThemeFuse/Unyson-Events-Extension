<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Events extends FW_Extension {
	private $post_type_name = 'fw-event';
	private $post_type_slug = 'fw-event-slug';
	private $taxonomy_name = 'fw-event-taxonomy-name';
	private $taxonomy_slug = 'fw-event-taxonomy-slug';
	private $taxonomy_tag_name = 'fw-event-tag';
	private $taxonomy_tag_slug = 'event-tag';

	/**
	 * @var string main option key
	 */
	private $event_option_id = 'general-event';

	public function get_event_option_id() {
		return $this->event_option_id;
	}

	public function fw_get_post_type_slug() {
		return $this->post_type_slug;
	}

	public function get_post_type_name() {
		return $this->post_type_name;
	}

	public function get_taxonomy_name() {
		return $this->taxonomy_name;
	}

	public function _get_link() {
		return self_admin_url( 'edit.php?post_type=' . $this->get_post_type_name() );
	}

	/**
	 * @internal
	 */
	protected function _init() {
		$this->define_slugs();
		$this->register_post_type();
		$this->register_taxonomy();

		if ( is_admin() ) {
			$this->save_permalink_structure();
			$this->add_admin_filters();
			$this->add_admin_actions();
		} else {
			$this->add_theme_actions();
		}

		add_filter( 'fw_post_options', array( $this, '_filter_fw_post_options' ), 10, 2 );
	}

	private function save_permalink_structure() {
		if ( ! isset( $_POST['permalink_structure'] ) && ! isset( $_POST['category_base'] ) ) {
			return;
		}

		$this->set_db_data(
			'permalinks/post',
			FW_Request::POST(
				'fw_ext_events_event_slug',
				apply_filters( 'fw_ext_' . $this->get_name() . '_post_slug', $this->post_type_slug )
			)
		);
		$this->set_db_data(
			'permalinks/taxonomy',
			FW_Request::POST(
				'fw_ext_events_taxonomy_slug',
				apply_filters( 'fw_ext_' . $this->get_name() . '_taxonomy_slug', $this->taxonomy_slug )
			)
		);
	}

	/**
	 * @internal
	 **/
	public function _action_add_permalink_in_settings() {
		add_settings_field(
			'fw_ext_events_event_slug',
			__( 'Event base', 'fw' ),
			array( $this, '_event_slug_input' ),
			'permalink',
			'optional'
		);

		add_settings_field(
			'fw_ext_events_taxonomy_slug',
			__( 'Events category base', 'fw' ),
			array( $this, '_taxonomy_slug_input' ),
			'permalink',
			'optional'
		);
	}

	/**
	 * @internal
	 */
	public function _event_slug_input() {
		?>
		<input type="text" name="fw_ext_events_event_slug" value="<?php echo $this->post_type_slug; ?>">
		<code>/my-event</code>
		<?php
	}

	/**
	 * @internal
	 */
	public function _taxonomy_slug_input() {
		?>
		<input type="text" name="fw_ext_events_taxonomy_slug" value="<?php echo $this->taxonomy_slug; ?>">
		<code>/my-events-category</code>
		<?php
	}

	private function define_slugs() {
		$this->post_type_slug = $this->get_db_data(
			'permalinks/post',
			apply_filters( 'fw_ext_' . $this->get_name() . '_post_slug', $this->post_type_slug )
		);
		$this->taxonomy_slug  = $this->get_db_data(
			'permalinks/taxonomy',
			apply_filters( 'fw_ext_' . $this->get_name() . '_taxonomy_slug', $this->taxonomy_slug )
		);
	}

	private function register_post_type() {
		$post_names = apply_filters( 'fw_ext_' . $this->get_name() . '_post_type_name',
			array(
				'singular' => __( 'Event', 'fw' ),
				'plural'   => __( 'Events', 'fw' )
			) );

		register_post_type( $this->post_type_name,
			array(
				'labels'             => array(
					'name'               => __( 'Events', 'fw' ),
					'singular_name'      => __( 'Event', 'fw' ),
					'add_new'            => __( 'Add New', 'fw' ),
					'add_new_item'       => sprintf( __( 'Add New %s', 'fw' ), $post_names['singular'] ),
					'edit'               => __( 'Edit', 'fw' ),
					'edit_item'          => sprintf( __( 'Edit %s', 'fw' ), $post_names['singular'] ),
					'new_item'           => sprintf( __( 'New %s', 'fw' ), $post_names['singular'] ),
					'all_items'          => sprintf( __( 'All %s', 'fw' ), $post_names['plural'] ),
					'view'               => sprintf( __( 'View %s', 'fw' ), $post_names['singular'] ),
					'view_item'          => sprintf( __( 'View %s', 'fw' ), $post_names['singular'] ),
					'search_items'       => sprintf( __( 'Search %s', 'fw' ), $post_names['plural'] ),
					'not_found'          => sprintf( __( 'No %s Found', 'fw' ), $post_names['plural'] ),
					'not_found_in_trash' => sprintf( __( 'No %s Found In Trash', 'fw' ), $post_names['plural'] ),
					'parent_item_colon'  => '' /* text for parent types */
				),
				'description'        => __( 'Create a event item', 'fw' ),
				'public'             => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'publicly_queryable' => true,
				/* queries can be performed on the front end */
				'has_archive'        => true,
				'rewrite'            => array(
					'slug' => $this->post_type_slug
				),
				'menu_position'      => 5,
				'show_in_nav_menus'  => true,
				'menu_icon'          => 'dashicons-calendar',
				'hierarchical'       => false,
				'query_var'          => true,
				/* Sets the query_var key for this post type. Default: true - set to $post_type */
				'supports'           => array(
					'title', /* Text input field to create a post title. */
					'editor',
					'thumbnail', /* Displays a box for featured image. */
					'revisions'
				)
			) );
	}

	private function register_taxonomy() {
		$category_names = apply_filters( 'fw_ext_' . $this->get_name() . '_category_name',
			array(
				'singular' => __( 'Category', 'fw' ),
				'plural'   => __( 'Categories', 'fw' )
			) );

		register_taxonomy( $this->taxonomy_name, $this->post_type_name, array(
			'labels'            => array(
				'name'              => sprintf( _x( 'Event %s', 'taxonomy general name', 'fw' ),
					$category_names['plural'] ),
				'singular_name'     => sprintf( _x( 'Event %s', 'taxonomy singular name', 'fw' ),
					$category_names['singular'] ),
				'search_items'      => sprintf( __( 'Search %s', 'fw' ), $category_names['plural'] ),
				'all_items'         => sprintf( __( 'All %s', 'fw' ), $category_names['plural'] ),
				'parent_item'       => sprintf( __( 'Parent %s', 'fw' ), $category_names['singular'] ),
				'parent_item_colon' => sprintf( __( 'Parent %s:', 'fw' ), $category_names['singular'] ),
				'edit_item'         => sprintf( __( 'Edit %s', 'fw' ), $category_names['singular'] ),
				'update_item'       => sprintf( __( 'Update %s', 'fw' ), $category_names['singular'] ),
				'add_new_item'      => sprintf( __( 'Add New %s', 'fw' ), $category_names['singular'] ),
				'new_item_name'     => sprintf( __( 'New %s Name', 'fw' ), $category_names['singular'] ),
				'menu_name'         => sprintf( __( '%s', 'fw' ), $category_names['plural'] )
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'rewrite'           => array(
				'slug' => $this->taxonomy_slug
			),
		) );

		/**
		 * @since 1.0.11
		 */
		if ( apply_filters('fw:ext:events:enable-tags', false) ) {
			$tag_names = apply_filters( 'fw_ext_events_tag_name', array(
				'singular' => __( 'Tag', 'fw' ),
				'plural'   => __( 'Tags', 'fw' )
			) );

			register_taxonomy($this->taxonomy_tag_name, $this->post_type_name, array(
				'hierarchical' => false,
				'labels' => array(
					'name'              => $tag_names['plural'],
					'singular_name'     => $tag_names['singular'],
					'search_items'      => sprintf( __('Search %s','fw'), $tag_names['plural']),
					'popular_items'     => sprintf( __( 'Popular %s','fw' ), $tag_names['plural']),
					'all_items'         => sprintf( __('All %s','fw'), $tag_names['plural']),
					'parent_item'       => null,
					'parent_item_colon' => null,
					'edit_item'         => sprintf( __('Edit %s','fw'), $tag_names['singular'] ),
					'update_item'       => sprintf( __('Update %s','fw'), $tag_names['singular'] ),
					'add_new_item'      => sprintf( __('Add New %s','fw'), $tag_names['singular'] ),
					'new_item_name'     => sprintf( __('New %s Name','fw'), $tag_names['singular'] ),
					'separate_items_with_commas'    => sprintf( __( 'Separate %s with commas','fw' ), strtolower($tag_names['plural'])),
					'add_or_remove_items'           => sprintf( __( 'Add or remove %s','fw' ), strtolower($tag_names['plural'])),
					'choose_from_most_used'         => sprintf( __( 'Choose from the most used %s','fw' ), strtolower($tag_names['plural'])),
				),
				'public' => true,
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => array(
					'slug' => $this->taxonomy_tag_slug
				),
			));
		}
	}

	private function add_admin_filters() {
		add_filter(
			'manage_' . $this->get_post_type_name() . '_posts_columns',
			array( $this, '_filter_add_columns' ),
			10,
			1
		);
		add_filter( 'months_dropdown_results', array( $this, '_filter_months_dropdown_results' ) );
	}

	private function add_admin_actions() {
		add_action(
			'manage_' . $this->get_post_type_name() . '_posts_custom_column',
			array( $this, '_action_manage_custom_column' ),
			10,
			2
		);
		add_action( 'admin_enqueue_scripts', array( $this, '_action_enqueue_scripts' ) );
		add_action( 'admin_head', array( $this, '_action_initial_nav_menu_meta_boxes' ), 999 );
		add_action( 'admin_init', array( $this, '_action_add_permalink_in_settings' ) );
	}

	private function add_theme_actions() {
		add_action( 'wp', array( $this, '_action_calendar_export' ) );
	}

	/**
	 * Modifies table structure for 'All Events' admin page
	 *
	 * @param $columns
	 *
	 * @return array
	 */
	public function _filter_add_columns( $columns ) {
		unset( $columns['date'], $columns[ 'taxonomy-' . $this->taxonomy_name ] );

		return array_merge( $columns,
			array(
				'event_date'     => __( 'Date', 'fw' ),
				'event_location' => __( 'Location', 'fw' )
			) );
	}

	/**
	 * Adds event options for it's custom post type
	 *
	 * @internal
	 *
	 * @param $post_options
	 * @param $post_type
	 *
	 * @return array
	 */
	public function _filter_fw_post_options( $post_options, $post_type ) {
		if ( $post_type !== $this->post_type_name ) {
			return $post_options;
		}

		$event_options = apply_filters( 'fw_ext_events_post_options',
			array(
				'events_tab' => array(
					'title'   => __( 'Event Options', 'fw' ),
					'type'    => 'tab',
					'options' => array(
						$this->event_option_id => array(
							'type'  => 'event',
							'desc'  => false,
							'label' => false,
						)
					)
				)
			) );

		if (empty($event_options)) {
			return $post_options;
		}

		if ( isset( $post_options['man'] ) && $post_options['main']['type'] === 'box' ) {
			$post_options['main']['options'][] = $event_options;
		} else {
			$post_options['main'] = array(
				'title'   => esc_html__( 'Event Settings', 'fw' ),
				'desc'    => false,
				'type'    => 'box',
				'options' => $event_options
			);
		}

		return $post_options;
	}

	public function _filter_months_dropdown_results( $months ) {
		$current_screen = array(
			'only' => array(
				array( 'post_type' => $this->post_type_name )
			)
		);

		return fw_current_screen_match( $current_screen ) ? array() : $months;
	}

	/**
	 * Fill custom column
	 *
	 * @internal
	 *
	 * @param $column
	 * @param $post_id
	 */
	public function _action_manage_custom_column( $column, $post_id ) {
		switch ( $column ) {
			case 'event_location' :
				echo $this->get_event_location( $post_id );
				break;
			case 'event_date' :
				echo $this->get_event_datetime_date( $post_id );
				break;
			default :
				break;
		}
	}

	/**
	 * Get saved event location array from db
	 *
	 * @param $post_id
	 *
	 * @return string
	 */
	private function get_event_location( $post_id ) {
		$meta = fw_get_db_post_option( $post_id, $this->event_option_id );

		return ( ( isset( $meta['event_location']['location'] ) and false === empty( $meta['event_location']['location'] ) ) ? $meta['event_location']['location'] : '&#8212;' );
	}

	/**
	 * A way to find out event start date
	 *
	 * @param $post_id int
	 *
	 * @return string
	 */
	private function get_event_datetime_date( $post_id ) {
		$meta      = fw_get_db_post_option( $post_id, $this->event_option_id );
		$empty_msg = '&#8212;';

		$result = $empty_msg;
		if ( isset( $meta['event_children'] ) && is_array( $meta['event_children'] ) ) {
			$meta_rows = fw_akg( 'event_children', $meta );
			if ( is_array( $meta_rows ) && count( $meta_rows ) > 0 ) {
				$min_date = null;
				$count    = 0;
				//search event's minimal from_date (also check if exists)
				foreach ( $meta_rows as $meta_row ) {
					$from_date = fw_akg( 'event_date_range/from', $meta_row );

					if ( empty( $from_date ) ) {
						continue;
					}

					try {
						$from_date = new DateTime( $from_date, new DateTimeZone( 'GMT' ) );
						if ( $min_date === null or $from_date->format( 'U' ) < $min_date->format( 'U' ) ) {
							$min_date = $from_date;
						}
						$count ++;
					} catch ( Exception $e ) {
						//if was saved wrong format
					}

				}

				if ( $count > 1 ) {
					$result = __( 'Multi Interval Event', 'fw' );
				} else {
					$result = empty( $min_date ) ? $empty_msg : $min_date->format( 'Y/m/d' );
				}
			}
		}

		return $result;
	}

	/**
	 * Enquee backend styles on events pages
	 *
	 * @internal
	 */
	public function _action_enqueue_scripts() {
		$current_screen = array(
			'only' => array(
				array( 'post_type' => $this->post_type_name )
			)
		);

		if ( fw_current_screen_match( $current_screen ) ) {
			wp_enqueue_style( 'fw-ext-events-css',
				$this->get_declared_URI( '/static/css/backend-events-style.css' ),
				array(),
				fw()->manifest->get_version()
			);
		}
	}

	/**
	 * @internal
	 */
	public function _action_initial_nav_menu_meta_boxes() {
		$screen = array(
			'only' => array(
				'base' => 'nav-menus'
			)
		);

		if ( ! fw_current_screen_match( $screen ) ) {
			return;
		}

		$user_ID = get_current_user_id();
		$meta    = fw_get_db_extension_user_data( $user_ID, $this->get_name() );

		if ( isset( $meta['metaboxhidden_nav-menus'] ) && $meta['metaboxhidden_nav-menus'] == true ) {
			return;
		}

		$hidden_meta_boxes = get_user_meta( $user_ID, 'metaboxhidden_nav-menus' );
		if ( $key = array_search( 'add-' . $this->taxonomy_name, $hidden_meta_boxes[0] ) ) {
			unset( $hidden_meta_boxes[0][ $key ] );
		}

		update_user_option( $user_ID, 'metaboxhidden_nav-menus', $hidden_meta_boxes[0], true );

		if ( ! is_array( $meta ) ) {
			$meta = array();
		}

		if ( ! isset( $meta['metaboxhidden_nav-menus'] ) ) {
			$meta['metaboxhidden_nav-menus'] = true;
		}

		fw_set_db_extension_user_data( $user_ID, $this->get_name(), $meta );
	}

	/**
	 * @intenral
	 */
	public function _action_calendar_export() {
		global $post;
		if ( empty( $post ) or $post->post_type !== $this->post_type_name ) {
			return;
		}

		if ( FW_Request::GET( 'calendar' ) ) {
			$calendar = FW_Request::GET( 'calendar' );
			$row_id   = FW_Request::GET( 'row_id' );
			$offset   = FW_Request::GET( 'offset' );
			$options  = fw_get_db_post_option( $post->ID, $this->get_event_option_id() );

			if ( ! is_array( fw_akg( 'event_children/' . $row_id, $options ) ) or ! preg_match( '/^\d+$/', $row_id ) ) {
				wp_redirect( site_url() . '?error=404' );
			}

			if ( ! preg_match( '/^(\-|\d)\d+$/', $offset ) ) {
				$offset = 0;
			}

			switch ( $calendar ) {
				case 'google':
					wp_redirect( $this->get_google_uri( $post, $options, $row_id, $offset ) );
					break;
				default:
					$this->get_ics_headers( $post );
					echo $this->get_ics_content( $post, $options, $row_id, $offset );
					die();
			}
		}
	}

	private function get_google_uri( $post, $options, $row_id, $offset ) {
		$all_day = fw_akg( 'all_day', $options, 'yes' );

		$date_template = 'Ymd';
		if ( $all_day === 'no' ) {
			$date_template = 'Ymd\THis\Z';
		}

		$start    = date( $date_template,
			$offset + strtotime( fw_akg( 'event_children/' . $row_id . '/event_date_range/from', $options, 'now' ) ) );
		$end      = date( $date_template,
			$offset + strtotime( fw_akg( 'event_children/' . $row_id . '/event_date_range/to', $options, 'now' ) ) );
		$location = fw_akg( 'event_location/location', $options, '' );

		return 'https://www.google.com/calendar/render?action=TEMPLATE&text=' . $post->post_title .
		       '&dates=' . $start . '/' . $end .
		       '&details=For+details,+link+here:+' . get_permalink( $post->ID ) .
		       '&location=' . $location;
	}

	private function get_ics_headers( $post ) {
		header( 'Content-type: text/calendar' );
		header( 'Content-Disposition: attachment; filename=' . urlencode( $post->post_title ) . '-' . time() . '.ics' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
	}

	private function get_ics_content( $post, $options, $row_id, $offset ) {
		$all_day = fw_akg( 'all_day', $options, 'yes' );

		$date_template = 'Ymd\T000000';
		if ( $all_day === 'no' ) {
			$date_template = 'Ymd\THis\Z';
		}

		$start    = date( $date_template,
			$offset + strtotime( fw_akg( 'event_children/' . $row_id . '/event_date_range/from', $options, 'now' ) ) );
		$end      = date( $date_template,
			$offset + strtotime( fw_akg( 'event_children/' . $row_id . '/event_date_range/to', $options, 'now' ) ) );
		$location = fw_akg( 'event_location/location', $options, '' );

		return "BEGIN:VCALENDAR\n" .
		       "VERSION:1.0\n" .
		       "BEGIN:VEVENT\n" .
		       "URL:" . get_permalink( $post->ID ) . "\n" .
		       "DTSTART:" . $start . "\n" .
		       "DTEND:" . $end . "\n" .
		       "SUMMARY:" . $post->post_title . "\n" .
		       "DESCRIPTION:For details, click here:" . get_permalink( $post->ID ) . "\n" .
		       "LOCATION:" . $location . "\n" .
		       "END:VEVENT\n" .
		       "END:VCALENDAR";
	}
}
