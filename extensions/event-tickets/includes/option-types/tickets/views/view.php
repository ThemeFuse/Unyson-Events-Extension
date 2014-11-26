<?php if (!defined('FW')) die('Forbidden');
/**
 * @var array  $option
 * @var array  $data
 * @var string $id
 * @var array  $popup_options
 * @var array  $div_attr
 */

?>

<div <?php echo fw_attr_to_html($div_attr) ?>>
	<!-- templates -->
		<br class="fw-hidden fw-ticket-templates"
		    <?php if (isset($option['row-controls']) and !empty($option['row-controls']) and is_array($option['row-controls'])) : ?>
			    <?php foreach($option['row-controls'] as $ticket_type => $options ): ?>
				    data-template-<?php echo $ticket_type ?>="<?php echo fw_htmlspecialchars(fw_render_view( dirname(__FILE__) . '/row.php', array(
					    'ticket_value' => array(),
					    'option' => $option,
					    'id' => $id,
					    'row_id' => '###-ticket-row-increment-###',
					    'ticket_type' => $ticket_type
				    ) ) ); ?>"
				<?php endforeach; ?>
			<?php endif; ?>
			/>
	<!-- .templates -->

	<!-- first tab -->
	<div class="fw-option-tickets-tab first <?php echo empty($data['value']) ? '' : 'fw-tab-closed' ?>">
		<div class="msg center"><?php echo __('What type of ticket would you like to start with?', 'fw') ?></div>
		<div class="fw-buttons-holder">
		<div class="btn-group center fw-add-ticket-row" data-row-counter="0">
			<?php if (isset($option['row-controls']) and false === empty($option['row-controls'])): ?>
				<?php foreach($option['row-controls'] as $ticket_type => $type_settings ) : ?>

					<?php $title = ucfirst(esc_html($ticket_type)); ?>
					<?php if ( array_key_exists('button', $type_settings) and array_key_exists('title', $type_settings['button']) ) : ?>
						<?php $title = $type_settings['button']['title']; ?>
					<?php endif; ?>
					<a class="fw-add-row-btn <?php echo isset($type_settings['button']['class']) ? esc_html($type_settings['button']['class']) : '' ?>" data-ticket-type="<?php echo esc_html($ticket_type) ?>"><?php echo $title ?></a>

				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		</div>
	</div>
	<!-- .first tab -->

	<!-- second tab -->
	<div class="fw-option-tickets-tab second <?php echo empty($data['value']) ? 'fw-tab-closed' : '' ?>">

		<div class="fw-tickets-table" >
			<div class="fw-tickets-thead">
				<div class="fw-ticket-row">
					<div class="fw-ticket-cell fw-ticket-gripper"></div>
					<?php if ( isset($option['header-controls']) and  false === empty($option['header-controls'])  and is_array($option['header-controls']) ) : ?>
						<?php foreach($option['header-controls'] as $class => $col_header): ?>
							<div class="fw-ticket-cell <?php echo esc_html($class) ?>"><label class="<?php echo isset($col_header['class']) ? esc_html($col_header['class']) : '' ?>" ><?php echo $col_header['title'] ?></label></div>
						<?php endforeach; ?>
					<?php endif; ?>

					<div class="fw-ticket-cell actions"><label><?php echo __('Actions', 'fw') ?></label></div>
				</div>
			</div>
			<div class="fw-tickets-tbody">
				<!-- data rows -->
				<?php $i = 0; ?>
				<?php foreach($data['value'] as $tiket_value ) : ?>
					<?php $i++; ?>
					<?php if (isset($tiket_value['type'])  and false === empty($tiket_value['type']) ) : ?>

						<?php echo fw_render_view( dirname(__FILE__) . '/row.php', array(
							'ticket_value' => $tiket_value,
							'option' => $option,
							'ticket_type' => $tiket_value['type'],
							'id' => $id,
							'row_id' => $i,
							) ); ?>

					<?php endif; ?>
				<?php endforeach; ?>
				<!-- .data rows -->
			</div>
		</div>

		<div class="fw-buttons-holder">
		<div class="btn-group left fw-add-ticket-row" data-row-counter="<?php echo $i ?>">
			<?php if (isset($option['row-controls']) and false === empty($option['row-controls'])): ?>
				<?php foreach($option['row-controls'] as $ticket_type => $type_settings ) : ?>

					<?php $title = ucfirst(esc_html($ticket_type)); ?>
					<?php if ( array_key_exists('button', $type_settings) and array_key_exists('title', $type_settings['button']) ) : ?>
						<?php $title = $type_settings['button']['title']; ?>
					<?php endif; ?>
						<a class="fw-add-row-btn <?php echo isset($type_settings['button']['class']) ? esc_html($type_settings['button']['class']) : '' ?>" data-ticket-type="<?php echo esc_html($ticket_type) ?>"><?php echo $title ?></a>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		</div>
	</div>
	<!-- .second tab -->

</div>