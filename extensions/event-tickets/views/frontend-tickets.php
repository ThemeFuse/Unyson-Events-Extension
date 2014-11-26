<?php if (!defined('FW')) die('Forbidden');

/**
 * @var array $option_values
 */
?>

<div class="unyson-tickets-wraper">
	<select class="unyson-tickets-list">
	<?php foreach($option_values as $key => $value) :?>
		<option value="<?php echo $key ?>"><?php echo $value[ fw()->backend->option_type( 'tickets' )->get_type() . '-name'] ?></option>
	<?php endforeach; ?>
	</select>
</div>