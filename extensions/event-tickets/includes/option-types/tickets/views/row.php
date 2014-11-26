<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $option
 * @var string $id
 * @var array $ticket_value
 * @var int $row_id
 * @var string $ticket_type
 *
 */

?>

<div class="fw-ticket-row">

	<div class="fw-ticket-cell fw-ticket-gripper">
		<i class="fa fa-unsorted"></i>
	</div>

	<?php if ( isset( $option['header-controls'] ) and false === empty( $option['header-controls'] ) and is_array( $option['header-controls'] ) ) : ?>
		<?php foreach ( $option['header-controls'] as $field_id => $header ) : ?>

			<div class="fw-ticket-cell <?php echo $field_id; ?>">

				<?php
				$ticket_data = array(
					'value'       => isset( $ticket_value[ $field_id ] ) ? $ticket_value[ $field_id ] : null,
					'name_prefix' => $option['attr']['name'] . '[' . $row_id . ']'
				);
				?>

				<div class="fw-ticket-cell-inner-option">
					<?php if ( isset( $option['row-controls'][ $ticket_type ]['row-options'][ $field_id ] ) and isset( $option['row-controls'][ $ticket_type ]['row-options'][ $field_id ]['type'] ) ) : ?>
						<?php echo fw()->backend->option_type( $option['row-controls'][ $ticket_type ]['row-options'][ $field_id ]['type'] )->render(  $field_id, $option['row-controls'][ $ticket_type ]['row-options'][ $field_id ], $ticket_data ); ?>
					<?php else : ?>
						<?php echo fw()->backend->option_type( 'html' )->render( $field_id, array(
							'type'  => 'html',
							'label' => false,
							'desc'  => false,
							'value' => '',
							'html'  => '&nbsp;',
						), $ticket_data ); ?>
					<?php endif; ?>
				</div>


					<!-- tooltip -->
					<?php if ( isset( $option['row-controls'][ $ticket_type ]['row-options'][ $field_id ]['help'] ) ) : ?>
						<div class="fw-option-help dashicons dashicons-info"
						     title="<?php echo $option['row-controls'][ $ticket_type ]['row-options'][ $field_id ]['help'] ?>"></div>
					<?php endif; ?>
					<!-- .tooltip -->
				</div>


		<?php endforeach; ?>
	<?php endif; ?>

	<div class="fw-ticket-cell actions">
		<?php if ( isset( $option['row-controls'][ $ticket_type ]['popup-controls']['options'] ) and is_array( $option['row-controls'][ $ticket_type ]['popup-controls']['options'] ) and false === empty( $option['row-controls'][ $ticket_type ]['popup-controls']['options'] ) ) : ?>
			<?php echo fw()->backend->option_type( 'popup' )->render( 'extra_settings', array(
					'type'          => 'popup',
					'value'         => array(),
					'label'         => fw_akg( 'row-controls/' . $ticket_type . '/popup-controls/title', $option, false ),
					'popup-title'   => false,
					'button'        => '',
					'attr'          => array(
						'class' => 'fw-actions-row-popup'
					),
					'popup-options' => $option['row-controls'][ $ticket_type ]['popup-controls']['options']
				),
				array(
					'value'       => isset( $ticket_value['extra_settings'] ) ? $ticket_value['extra_settings'] : null,
					'name_prefix' => $option['attr']['name'] . '[' . $row_id . ']'
				)
			) ?>
		<?php endif; ?>
		<div class="fw-actions-remove-row fa fa-times"></div>
	</div>

	<div class="fw-hidden">
		<?php
		$ticket_data = array(
			'value'       => $ticket_type,
			'name_prefix' => $option['attr']['name'] . '[' . $row_id . ']'
		);
		?>

		<?php echo fw()->backend->option_type( 'hidden' )->render( 'type', array(
			'type'  => 'hidden',
			'value' => $ticket_type,
		), $ticket_data ); ?>

		<?php
		$ticket_data = array(
			'value'       => isset( $ticket_value['hash'] ) ? $ticket_value['hash'] : null,
			'name_prefix' => $option['attr']['name'] . '[' . $row_id . ']'
		);
		?>

		<?php echo fw()->backend->option_type( 'hidden' )->render( 'hash', array(
			'type'  => 'hidden',
			'value' => '',
		), $ticket_data ); ?>
	</div>
</div>
