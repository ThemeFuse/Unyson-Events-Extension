<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

interface FW_Events_Interface_Tabs {
	/**
	 * @return array(
	 *           'events_tab' => array(
	 *               'title'   => __( 'New Tab Options', 'fw' ),
	 *               'type'    => 'tab',
	 *               'options' => array(
	 *                      'unique_id' => array(
	 *                          'type'  => 'event',
	 *                          'desc'  => false,
	 *                          'label' => false,
	 *            )
	 *         )
	 *      )
	 *   )
	 */

	public function fw_get_tabs_options();
}
