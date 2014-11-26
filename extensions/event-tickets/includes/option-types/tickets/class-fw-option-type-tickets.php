<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}


class FW_Option_Type_Tickets extends FW_Option_Type {
	private static $extension;

	public function get_type() {
		return 'tickets';
	}

	/**
	 * @internal
	 */
	public function _init() {
		$ext             = fw()->extensions->get( 'event-tickets' );
		self::$extension = array(
			'path' => $ext->get_declared_path(),
			'URI'  => $ext->get_declared_URI()
		);
	}


	private function _fw_prepare_option( $options ) {
		$result = array();
		foreach ( $options['header-controls'] as $key => $title ) {
			$result['header-controls'][ $this->get_type() . '-' . $key ] = $title;
		}

		foreach ( $options['row-controls'] as $type => $controls ) {
			$result['row-controls'][ $type ]['popup-controls'] = isset( $controls['popup-controls'] ) ? $controls['popup-controls'] : array();
			$result['row-controls'][ $type ]['button']         = isset( $controls['button'] ) ? $controls['button'] : array();
			foreach ( $controls['row-options'] as $key => $option ) {
				$result['row-controls'][ $type ]['row-options'][ $this->get_type() . '-' . $key ] = $option;
			}
		}

		return array_merge( $options, $result );
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static( $id, $option, $data ) {
		wp_enqueue_style(
			$this->get_type() . '-styles',
			self::$extension['URI'] . '/includes/option-types/' . $this->get_type() . '/static/css/styles.css',
			array( 'qtip', 'fw-font-awesome' ),
			fw()->manifest->get_version()
		);

		wp_enqueue_script(
			$this->get_type() . '-scripts',
			self::$extension['URI'] . '/includes/option-types/' . $this->get_type() . '/static/js/scripts.js',
			array( 'jquery', 'fw-events', 'underscore', 'jquery-ui-sortable', 'qtip' ),
			fw()->manifest->get_version(),
			true
		);


		//enquee styles & scripts for popup option type
		if ( isset( $option['row-controls'] ) and is_array( $option['row-controls'] ) ) {
			foreach ( $option['row-controls'] as $controls ) {
				if ( isset( $controls['popup-controls']['options'] ) and is_array( $controls['popup-controls']['options'] ) ) {
					fw()->backend->enqueue_options_static( $controls['popup-controls']['options'] );
				}
			}
		}

		wp_localize_script( 'tickets-scripts', 'removeRowConfirmMsg', __( 'Are you sure you want to delete this ticket?', 'fw' ) );

	}

	/**
	 * @internal
	 */
	protected function _render( $id, $option, $data ) {
		$option = $this->_fw_prepare_option( $option );
		$this->_fw_prepare_data( $data, $option );

		$div_attr = $option['attr'];
		unset( $div_attr['name'], $div_attr['value'] );

		return fw_render_view( dirname( __FILE__ ) . '/views/view.php', array(
			'id'       => $id,
			'option'   => $option,
			'data'     => $data,
			'div_attr' => $div_attr,
		) );
	}

	/**
	 * Remove from data value disallowed ticket types
	 * @internal
	 */
	private function _fw_prepare_data( &$data, $option ) {
		$allowed_tickets = array_keys( $option['row-controls'] );
		foreach ( $data['value'] as $key => $ticket_data ) {
			if ( in_array( $ticket_data['type'], $allowed_tickets ) ) {
				continue;
			}
			unset( $data['value'][ $key ] );
		}
	}

	/**
	 * @internal
	 */
	protected function _get_value_from_input( $option, $input_value ) {

		$option = $this->_fw_prepare_option( $option );

		$available_ticket_types = array_keys( $option['row-controls'] );

		$value = array();
		if ( is_array( $input_value ) ) {
			foreach ( $input_value as $key => &$input_value_item ) {
				//remove invalid types
				if ( ! isset( $input_value_item['type'] ) or empty( $input_value_item['type'] ) or ! in_array( $input_value_item['type'], $available_ticket_types ) ) {
					unset( $input_value_item[ $key ] );
					continue;
				}

				$current_value         = array();
				$current_value['type'] = $input_value_item['type'];
				//todo: some hash validation
				$current_value['hash'] = $input_value_item['hash']; //option type hidden


				//call get_value_from_input for row options
				{
					$row_options = fw_extract_only_options( $option['row-controls'][ $input_value_item['type'] ]['row-options'] );
					foreach ( $row_options as $id => $input_option ) {
						$current_value[ $id ] = fw()->backend->option_type( $input_option['type'] )->get_value_from_input(
							$input_option,
							isset( $input_value_item[ $id ] ) ? $input_value_item[ $id ] : null
						);
					}
				}

				//todo: call get_value_from_input for popup options ??
				{
					$current_value['extra_settings'] = $input_value_item['extra_settings'];
				}

				$value[] = $current_value;
			}

		}

		return $value;
	}

	/**
	 * @internal
	 */
	public function _get_backend_width_type() {
		return 'full';
	}

	/**
	 * @internal
	 */
	protected function _get_defaults() {
		return array(
			'value' => array()
		);
	}

}

FW_Option_Type::register( 'FW_Option_Type_Tickets' );