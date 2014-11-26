<?php if (!defined('FW')) die('Forbidden');


class FW_Extension_Event_Tickets extends FW_Extension implements FW_Events_Interface_Tabs {

	private $ticket_option_id = 'fw-ticket';
	private $internal_rows_options;
	private $internal_headers;

	/**
	 * @internal
	 */
	protected function _init()
	{
		$this->_fw_fill_row_options();
		$this->_fw_fill_column_headers();

	if (!is_admin()) {
			$this->_fw_add_theme_actions();
		}
	}


	private function _fw_fill_column_headers() {
		$this->internal_headers =  array(
			'name' => array(
				'title'    => __('Ticket Name', 'fw'),
				'class'    => 'required',
			),
			'quantity' => array(
				'title'    => __('Ticket Quantity', 'fw'),
				'class'    => 'required',
			),
			'price' => array(
				'title' => sprintf(__('Ticket Price in %s', 'fw'), 'USD'),
			),
		);

		return $this->internal_headers;
	}

	private function _fw_fill_row_options(){
		$popup_controls = array(
			'title' =>  __('Settings for Press Invitations', 'fw'),
			'options' => array(
				'description' => array(
					'type'  => 'textarea',
					'label' => __('Ticket Description', 'fw'),
					'desc' => false,
					'value' => ''
				),
				'sale_date_range' => array(
					'type'  => 'datetime-range',
					'label' => __('Sale\'s Date & Time', 'fw'),
					'desc'  => __('Set sales start and end date & time','fw'),
				),
				'visibility' => array(
					'label' => __('Ticket Visibility', 'fw'),
					'type'  => 'checkbox',
					'value' => true,
					'desc'  => false,
					'text'  => __('Hide this ticket type', 'fw'),
				),
				'order_allowed_qty' => array(
					'label' => __('Tickets Allowed Per Order', 'fw'),
					'type'  => 'text',
					'desc'  => __('Maximum number of tickets allowed per order', 'fw'),
					'value' => 1
				)
			)
		);

		$this->internal_rows_options = array(
			'free' => array(
				'button' => array(
					'title' => __('Free Ticket', 'fw'),
					'class' => 'btn button-primary fw-btn-large'
				),
				'popup-controls' => $popup_controls,
				'row-options' => array(
					'name' => array(
						'type'  => 'text',
						'label' => false,
						'desc' => false,
						'value' => '',
					),
					'quantity' => array(
						'type'  => 'text',
						'label' => false,
						'desc' => false,
						'value' => '',
					),
					'price' => array(
						'type'  => 'html',
						'label' => false,
						'desc' => false,
						'value' => '',
						'html'  => '<label>' . __('Free','fw') . '<label>',
					),
				)
			),
			'paid' => array(
				'button' => array(
					'title' => __('Paid Ticket'),
					'class' => 'btn button-primary fw-btn-large'
				),
				'popup-controls' => $popup_controls,
				'row-options' => array(
					'name' => array(
						'type'  => 'text',
						'label' => false,
						'desc' => false,
						'value' => '',

					),
					'quantity' => array(
						'type'  => 'text',
						'label' => false,
						'desc' => false,
						'value' => '',
					),
					'price' => array(
						'type'  => 'text',
						'label' => false,
						'desc' => false,
						'value' => 0,
						'help' => esc_html("Price: 0<br/>Fee: 0<hr/>Total: 0"),
					),
				)

			),
			'donation' => array(
				'button' => array(
					'title' => __('Donation'),
					'class' => 'button fw-btn-large'
				),
				'popup-controls' => $popup_controls,
				'row-options' => array(
					'name' => array(
						'type'  => 'text',
						'label' => false,
						'desc' => false,
						'value' => '',

					),
					'quantity' => array(
						'type'  => 'text',
						'label' => false,
						'desc' => false,
						'value' => '',
					),
					'price' => array(
						'type'  => 'html',
						'label' => false,
						'desc' => false,
						'value' => 0,
						'html'  => '<label>' . __('Donation','fw') . '<label>',
					),
				)

			)
		);

		return $this->internal_rows_options;
	}

	private function _fw_add_theme_actions() {
		add_action( 'fw_theme_ext_events_after_content', array($this, '_action_theme_render_tickets') );
	}

	public function _action_theme_render_tickets() {
		global $post;
		if ( empty($post) or $post->post_type !== $this->get_parent()->get_post_type_name() ) {
			return;
		}
		$option_values = fw_get_db_post_option($post->ID, $this->ticket_option_id);
		if (!empty($option_values) and is_array($option_values)) {
			echo fw_render_view($this->locate_path('/views/frontend-tickets.php'), array('option_values' => $option_values) );
		}
	}

	public function fw_get_tabs_options()
	{
		$tabs[$this->get_name(). '_tab'] = array(
			'title' => __('Tickets', 'fw'),
			'type'  => 'tab',
			'options' => array(
				$this->ticket_option_id => array(
					'type' => 'tickets',
					'desc' => false,
					'label' => false,
					'header-controls' => $this->internal_headers,
					'row-controls' => $this->get_allowed_options()
				),
			)
		);

		return $tabs;
	}

	private function get_allowed_options()
	{
		$allowed_tickets = $this->get_config('allowed_tickets');

		if ( empty($allowed_tickets) or empty($this->internal_rows_options) or false === is_array($allowed_tickets) or false === is_array($this->internal_rows_options) ) {
			return array();
		}

		foreach($this->internal_rows_options as $key => $ticket_options) {
			if (in_array($key, $allowed_tickets)){
				continue;
			}
			unset($this->internal_rows_options[$key]);
		}

		return $this->internal_rows_options;
	}

	public function get_ticket_option_id(){
		return $this->ticket_option_id;
	}

}