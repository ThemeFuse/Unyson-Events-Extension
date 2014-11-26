(function ($, fwe, _) {

	//update help tooltip text
	var tooltipTemplate = _.template('Price: <%= price %><br/>Fee: <%= fee %><hr/>Total: <%  print( parseFloat(price)+parseFloat(fee) ) %> <%= currency %>');

	fwe.on('fw:options:event-tickets:initialized', function ($optionTickets) {
		$optionTickets.$element.on('change blur mouseout', '.tickets-price input', function () {
			var $qTip = $(this).closest('.fw-ticket-cell').find('.fw-option-help.initialized'),
				data = {price: $(this).val(), fee: 0, currency: 'USD' };
			$qTip.qtip('option', 'content.text', tooltipTemplate( data ));
			$qTip.attr('title', tooltipTemplate( data ));
		});
	});
})(jQuery, fwEvents, _);