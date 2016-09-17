<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Events_Tags extends FW_Extension {

	private $post_type_slug;
	private $post_type;
	private $data_provider_id = 'events';
	private $to_date = 'event-to-date';
	private $from_date = 'event-from-date';
	private $from_time = 'event-from-time';
	private $to_time = 'event-to-time';
	private $all_day = 'all_day_event';

	/**
	 * @var null|FW_Extension_Events
	 */
	private $parent = null;


	private function _fw_define_slugs() {
		$this->post_type      = apply_filters( 'fw_ext_' . $this->get_name() . '_post_slug',
			$this->parent->get_post_type_name() . '-search' );
		$this->post_type_slug = apply_filters( 'fw_ext_' . $this->get_name() . '_post_slug',
			$this->parent->fw_get_post_type_slug() . '-search' );
	}

	public function _init() {

		$this->parent = $this->get_parent();

		$this->_fw_define_slugs();

		$this->wpml_compatibilitty();

		add_action( 'init', array( $this, '_action_register_post_type_tags' ) );
		add_filter( 'fw_shortcode_calendar_provider', array( $this, '_filter_theme_shortcode_calendar_set_provider' ) );
		add_filter( 'fw_shortcode_map_provider', array( $this, '_filter_theme_shortcode_map_set_provider' ) );

		if ( is_admin() ) {
			$this->admin_actions();
		} else {
			$this->theme_filters();
		}
	}

	private function wpml_compatibilitty() {
		if ( ! class_exists( 'SitePress' ) ) {
			return;
		}

		global $sitepress_settings;
		$sitepress_settings['custom_posts_sync_option'][ $this->post_type ] = 1;

		add_filter( 'get_translatable_documents', array( $this, '_filter_wpml_make_post_type_translatable' ) );

		if ( is_admin() ) {
			add_action( 'icl_make_duplicate', array( $this, '_action_admin_on_save_event' ) );
		}
	}

	private function theme_filters() {
		add_filter( 'fw_shortcode_calendar_ajax_params', array( $this, '_filter_theme_shortcode_calendar_ajax_params' ),
			10, 3 );
	}

	/**
	 * Register extension events in shortcode "Map", with initial data
	 *
	 * @param array $value
	 *
	 * @return mixed
	 */
	public function _filter_theme_shortcode_map_set_provider( $value ) {
		$value[ $this->data_provider_id ] = array(
			'label'    => __( 'Events', 'fw' ),
			'callback' => array( $this, 'fw_get_events_locations' ),
			'options'  => array(
				'events_category' => array(
					'label'   => __( 'Event Categories', 'fw' ),
					'desc'    => __( 'Select an event category', 'fw' ),
					'type'    => 'select',
					'choices' => array( '' => __( 'All Events', 'fw' ) ) + $this->_fw_get_event_terms_choices()
				)
			)
		);

		return $value;
	}

	/**
	 * Get all/by_category event's locations from db
	 *
	 * @param array $atts
	 *
	 * @return array
	 */
	public function fw_get_events_locations( $atts ) {

		$category = fw_akg( 'data_provider/' . $this->data_provider_id . '/events_category', $atts );

		$args = array(
			'post_type'      => $this->parent->get_post_type_name(),
			'posts_per_page' => - 1,
			'post_status'    => 'publish'
		);

		// add taxonomy term query args
		{
			$terms_ids     = array();
			$with_category = false;
			if ( preg_match( '/^\d+$/', $category ) ) {
				$terms_ids = get_term_children( $category, $this->parent->get_taxonomy_name() );
				if ( is_array( $terms_ids ) and false === empty( $terms_ids ) and false === is_wp_error( $terms_ids ) ) {
					$terms_ids[] = (int) $category;
				} else {
					$terms_ids = array( $category );
				}
				$with_category = true;
			}

			if ( $with_category ) {
				$args['tax_query'] = array(
					array(
						'taxonomy' => $this->parent->get_taxonomy_name(),
						'field'    => 'id',
						'terms'    => $terms_ids,
						'operator' => 'IN'
					),
				);
			}
		}

		$query = new WP_Query( $args );
		$posts = $query->get_posts();
		wp_reset_query();

		$result = array();
		if ( is_array( $posts ) && count( $posts ) > 0 ) {
			foreach ( $posts as $key => $post ) {
				$meta     = fw_get_db_post_option( $post->ID, $this->parent->get_event_option_id() );
				$location = trim( fw_akg( 'event_location/location', $meta, '' ) );
				if ( false === empty( $location ) ) {
					$result[ $key ]['title']       = htmlspecialchars_decode( $post->post_title );
					$result[ $key ]['coordinates'] = fw_akg( 'event_location/coordinates', $meta, array() );
					$result[ $key ]['url']         = get_permalink( $post->ID );
					$result[ $key ]['thumb']       = fw_resize( wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) ),
						100, 60, true );
					$result[ $key ]['description'] = $location;
				}
			}
		}

		return $result;
	}

	/**
	 * @internal
	 *
	 * @param array $post_types
	 *
	 * @return array
	 **/
	public function _filter_wpml_make_post_type_translatable( $post_types ) {

		if ( ! isset( $post_types[ $this->post_type ] ) ) {
			$post_types[ $this->post_type ] = get_post_type_object( $this->post_type );
		}

		return $post_types;
	}

	/**
	 * Fill shortcode Calendar with initial data
	 *
	 * @internal
	 *
	 * @param $value - list of data providers
	 *
	 * @return mixed
	 */
	public function _filter_theme_shortcode_calendar_set_provider( $value ) {
		$value[ $this->data_provider_id ] = array(
			'label'    => __( 'Events', 'fw' ),
			'callback' => array( $this, 'fw_get_events_by_range' ),
			'options'  => array(
				'events_category' => array(
					'label'   => __( 'Event Categories', 'fw' ),
					'desc'    => __( 'Select an event category', 'fw' ),
					'type'    => 'select',
					'choices' => array( '' => __( 'All Events', 'fw' ) ) + $this->_fw_get_event_terms_choices()
				)
			)
		);

		return $value;
	}

	/**
	 * Saved option 'events_category' sets as ajax parameter
	 *
	 * @internal
	 *
	 * @param array $value - presetted ajax parameters (e.g. array('ajax_post_param' => 'string value') )
	 * @param string $provider - choosen data provider
	 * @param array $option_values - user saved option values
	 *
	 * @return array                 - ajax parameters (e.g. array('ajax_post_param' => 'string value') )
	 */
	public function _filter_theme_shortcode_calendar_ajax_params( $value, $provider, $option_values ) {
		if ( $provider === $this->data_provider_id ) {
			if ( is_array( $value ) ) {
				return array_merge( $value, $option_values );
			}

			return $option_values;
		}

		return $value;
	}

	/**
	 * Generate array of terms for option choices
	 */
	private function _fw_get_event_terms_choices() {
		$terms = get_terms( $this->parent->get_taxonomy_name(), array(
			'hide_empty' => 0
		) );

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		$result = array();
		if ( is_array( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$name                     = trim( $term->name );
				$result[ $term->term_id ] = empty( $name ) ? $term->slug : $name;
			}
		}

		return $result;
	}

	private function admin_actions() {
		add_action( 'fw_save_post_options', array( $this, '_action_admin_on_save_event' ) );
		add_action( 'before_delete_post', array( $this, '_action_admin_on_delete_event' ) );

		$time = (int) $this->get_db_data( 'last_updated', 0 );

		if ( ( time() - $time ) > ( 86400 + 5 ) ) {
			$this->update_events();
			$this->set_db_data( 'last_updated', time() );
		}
	}

	/**
	 * @internal
	 *
	 * @param $post_id
	 */
	public function _action_admin_on_save_event( $post_id ) {
		if ( get_post_type( $post_id ) !== $this->parent->get_post_type_name() or ! fw_is_real_post_save( $post_id ) ) {
			return;
		}

		$this->_fw_remove_all_event_children_data( $post_id );
		$this->_fw_insert_all_event_children_data( $post_id );
	}

	/**
	 * @internal
	 *
	 * @param $post_id
	 */
	public function _action_admin_on_delete_event( $post_id ) {
		if ( get_post_type( $post_id ) !== $this->parent->get_post_type_name() ) {
			return;
		}
		$this->_fw_remove_all_event_children_data( $post_id );
	}

	/**
	 * @internal
	 */
	public function _action_register_post_type_tags() {

		register_post_type( $this->post_type, array(
			'labels'              => false,
			'description'         => false,
			'public'              => false,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_admin_bar'   => false,
			'has_archive'         => false,
			'rewrite'             => array(
				'slug' => $this->post_type_slug
			),
			'show_in_nav_menus'   => false,
			'hierarchical'        => true,
			'query_var'           => false,
			'supports'            => array(
				'author'
			)
		) );

	}

	/**
	 * Remove fw-event-tags posts from db related with fw-event post.
	 *
	 * @param int $post_id
	 */
	private function _fw_remove_all_event_children_data( $post_id ) {

		$args = array(
			'post_parent' => $post_id,
			'post_type'   => $this->post_type,
			'post_status' => 'any'
		);

		$posts = get_posts( $args );

		if ( is_array( $posts ) && count( $posts ) > 0 ) {

			foreach ( $posts as $post ) {
				wp_delete_post( $post->ID, true );
			}

		}

	}

	/**
	 * For even datetime range row create custom 'fw-events-tags' post. Also save search query tags as meta values.
	 *
	 * @param int $post_id
	 *
	 * @return bool
	 */
	private function _fw_insert_all_event_children_data( $post_id ) {
		$options_values = fw_get_db_post_option( $post_id );
		if ( is_array( $options_values ) === false ) {
			return false;
		}

		$container_id   = $this->parent->get_event_option_id();
		$meta_rows_data = fw_akg( $container_id . '/event_children', $options_values );
		$all_day_event  = fw_akg( $container_id . '/all_day', $options_values );

		if ( is_array( $meta_rows_data ) && count( $meta_rows_data ) > 0 ) {
			foreach ( $meta_rows_data as $meta_row ) {

				$start_date = fw_akg( 'event_date_range/from', $meta_row );
				$end_date   = fw_akg( 'event_date_range/to', $meta_row );

				$from_timestamp = strtotime( $start_date );
				$to_timestamp   = strtotime( $end_date );

				if ( ! $from_timestamp || ! $to_timestamp || - 1 === $from_timestamp || - 1 === $to_timestamp ) {
					continue;
				}

				$terms = wp_get_post_terms( $post_id, $this->parent->get_taxonomy_name(), array( 'fields' => 'ids' ) );

				$event_post_tag_id = wp_insert_post(
					array(
						'post_parent' => $post_id,
						'post_type'   => $this->post_type,
						'post_status' => 'publish',
						'tax_input'   => array(
							$this->parent->get_taxonomy_name() => $terms
						)
					), true );

				if ( $event_post_tag_id == 0 || $event_post_tag_id instanceof WP_Error ) {
					return false;
				}

				add_post_meta( $event_post_tag_id, $this->from_date,
					$from_timestamp - ( date( 'H', $from_timestamp ) * 3600 + date( 'i', $from_timestamp ) * 60 ) );
				add_post_meta( $event_post_tag_id, $this->to_date,
					$to_timestamp - ( date( 'H', $to_timestamp ) * 3600 + date( 'i', $to_timestamp ) * 60 ) );
				add_post_meta( $event_post_tag_id, $this->from_time,
					date( 'H', $from_timestamp ) * 3600 + date( 'i', $from_timestamp ) * 60 );
				add_post_meta( $event_post_tag_id, $this->to_time,
					date( 'H', $to_timestamp ) * 3600 + date( 'i', $to_timestamp ) * 60 );
				add_post_meta( $event_post_tag_id, $this->all_day, $all_day_event );

				if ( function_exists( 'wpml_get_language_information' ) && function_exists( 'wpml_add_translatable_content' ) ) {
					$lang     = wpml_get_language_information( $post_id );
					$language = substr( fw_akg( 'locale', $lang ), 0, 2 );

					if ( ! empty( $language ) ) {
						wpml_add_translatable_content( 'post_' . $this->post_type, $event_post_tag_id, $language );

					}
				}

				$users = fw_akg( 'event-user', $meta_row );
				if ( is_array( $users ) && count( $users ) > 0 ) {
					foreach ( $users as $user ) {
						add_post_meta( $event_post_tag_id, 'event-user', $user );
					}
				}
			}
		}

		return true;
	}

	/**
	 * @param $params array(
	 *                  'from' => 123234455      - (int) start (unixtime) range query
	 *                  'to'   => 435455645      - (int) end (unixtime) range query
	 *                  'template' => 'day'      - (string) group dates for template (day grouped sensitivity equal minutes, else )
	 *                  'events_category'        - (int) parent Events term id
	 *                )
	 *
	 * @return array
	 */
	public function fw_get_events_by_range( $params ) {
		$from = fw_akg( 'from', $params );
		$to   = fw_akg( 'to', $params );

		if ( empty( $from ) or empty( $to ) or ! preg_match( '/^\d+$/', $from ) or ! preg_match( '/^\d+$/',
				$to ) or ( $to < $from )
		) {
			return array();
		}

		$group_for = fw_akg( 'template', $params );
		$category  = fw_akg( 'events_category', $params );

		$terms_ids     = array();
		$with_category = false;
		if ( preg_match( '/^\d+$/', $category ) ) {
			$terms_ids = get_term_children( $category, $this->parent->get_taxonomy_name() );
			if ( is_array( $terms_ids ) and false === empty( $terms_ids ) and false === is_wp_error( $terms_ids ) ) {
				$terms_ids[] = (int) $category;
			} else {
				$terms_ids = array( $category );
			}
			$with_category = true;
		}

		$args = array(
			'post_type'      => $this->post_type,
			'posts_per_page' => - 1,
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => $this->from_date,
					'value'   => array( $from, $to ),
					'compare' => 'BETWEEN'
				),
				array(
					'key'     => $this->to_date,
					'value'   => array( $from, $to ),
					'compare' => 'BETWEEN'
				),
				array(
					'relation' => 'AND',
					array(
						'key'     => $this->from_date,
						'value'   => $from,
						'compare' => '<='
					),
					array(
						'key'     => $this->to_date,
						'value'   => $to,
						'compare' => '>='
					)
				)
			),
		);

		if ( $with_category ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => $this->parent->get_taxonomy_name(),
					'field'    => 'id',
					'terms'    => $terms_ids,
					'operator' => 'IN'
				)
			);
		}

		$posts = new WP_Query( $args );

		$items = $posts->get_posts();

		$result = array();
		if ( is_array( $items ) and count( $items ) > 0 ) {

			if ( $group_for === 'day' ) {
				$result = $this->_fw_prepare_data( $items, 'YmdHi' );
			} else {
				$result = $this->_fw_prepare_data( $items, 'Ymd' );
			}

		}

		return $result;
	}

	/**
	 * @internal
	 *
	 * @param int $offset
	 **/
	private function update_events( $offset = 0 ) {
		$posts = get_posts( array(
			'post_type'      => $this->parent->get_post_type_name(),
			'posts_per_page' => 100,
			'offset'         => $offset
		) );

		$offset += 100;

		if ( empty( $posts ) ) {
			return;
		}

		foreach ( $posts as $post ) {
			$this->_action_admin_on_save_event( $post->ID );
		}

		$this->update_events( $offset );
	}

	/**
	 * Prepare data structure compatible with shortcode Calendar
	 *
	 * @param WP_Post $items
	 *
	 * @param string $format
	 *
	 * @return array
	 */
	private function _fw_prepare_data( $items, $format = null ) {

		$result = array();

		foreach ( $items as $key => $item ) {

			//start datetime
			{
				$timestamp_start_date                          = get_post_meta( $item->ID, $this->from_date, true );
				$timestamp_start_time                          = get_post_meta( $item->ID, $this->from_time, true );
				$result[ $item->post_parent ][ $key ]['start'] = ( $timestamp_start_date + $timestamp_start_time );
			}

			//end datetime
			{
				$timestamp_end_date = get_post_meta( $item->ID, $this->to_date, true );
				$timestamp_end_time = ( strtolower( get_post_meta( $item->ID, $this->all_day,
					true ) ) === 'yes' ? 86399 : get_post_meta( $item->ID,
					$this->to_time, true ) );  // 23:59:59 86399 //86400 24:00:00

				$result[ $item->post_parent ][ $key ]['end'] = ( $timestamp_end_date + $timestamp_end_time );
			}

		}

		$result = $this->_fw_grouped_calendar_dates( $result, $format );

		$return_value = array();
		$i            = 0;
		//fill return value with shrortcode Calendar supported data structure
		foreach ( $result as $event_id => $intervals ) {

			if ( is_null( get_post( $event_id ) ) ) {
				continue;
			}

			$title = get_the_title( $event_id );
			$url   = get_permalink( $event_id );
			foreach ( $intervals as $interval ) {
				$return_value[ $i ]['start'] = $interval['start'];
				$return_value[ $i ]['end']   = $interval['end'];
				$return_value[ $i ]['id']    = $event_id;
				$return_value[ $i ]['title'] = htmlspecialchars_decode( $title );
				$return_value[ $i ]['url']   = $url;
				$i ++;
			}
		}

		return $return_value;
	}

	/**
	 * Merge event dates by datetime format
	 *
	 * @param $format string                                     - group accuracy (e.g. 'YmdHis' compatible with datetime formats)
	 * @param $main_event  array(
	 *                         '123' => array(                   - (int) post_parent sub event
	 *                             '333' => array(               - (int) any unique id
	 *                                 'start' => '1355270400'   - (int) unixtimestamp
	 *                                 'end'   => '1355270700'   - (int) unixtimestamp
	 *                              )
	 *                              ***
	 *                          )
	 *                          ***
	 *                      )
	 *
	 * @return array
	 */
	private function _fw_grouped_calendar_dates( $main_event, $format = 'Ymd' ) {
		foreach ( $main_event as &$sub_events_array ) {
			//sort sub events date ranges by 'start' ascending
			uasort( $sub_events_array, array( $this, 'fw_compare_event_dates' ) );

			$i                 = 0;
			$remove_items_keys = array();
			foreach ( $sub_events_array as &$sub_event ) {
				$i ++;

				//get next sub event date ranges
				$events_sliced = array_slice( $sub_events_array, $i, null, true );

				if ( empty( $events_sliced ) ) {
					continue;
				}

				//merge date ranges by date format
				foreach ( $events_sliced as $key_sliced => $sub_event_sliced ) {
					if ( date( $format, $sub_event_sliced['start'] ) <= date( $format, $sub_event['end'] ) ) {
						if ( date( $format, $sub_event_sliced['end'] ) >= date( $format, $sub_event['end'] ) ) {
							$sub_event['end'] = $sub_event_sliced['end'];
						}
						//save elements keys which will be removed
						$remove_items_keys[] = $key_sliced;
					}
				}
			}

			//clean not actual sub event date ranges
			if ( ! empty( $remove_items_keys ) ) {
				foreach ( $remove_items_keys as $key ) {
					unset( $sub_events_array[ $key ] );
				}
			}
		}

		return $main_event;
	}

	/**
	 * @internal
	 */
	public function fw_compare_event_dates( $a, $b ) {
		if ( $a['start'] == $b['start'] ) {
			return 0;
		} elseif ( $a['start'] > $b['start'] ) {
			return 1;
		}

		return - 1;
	}

}