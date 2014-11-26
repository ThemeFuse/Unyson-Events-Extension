(function($, _, fwe){
'use strict';
	var init = function(){

		var $wrapper = $(this),
			$ticketsContainer = $wrapper.find('.fw-tickets-tbody'),
			templates = {},
			increment = 0;

		var fixHelper = function(e, ui) {

			ui.addClass('fw-fix-border-top');

			ui.children().each(function() {
				$(this).width($(this).width());
			});
			return ui;
		};

		var methods = {
				readTemplatesHTML: function(){
					templates = $wrapper.find('.fw-ticket-templates').data();
				},
				removeTemplatesHolder: function(){
					$wrapper.find('.fw-ticket-templates').remove();
				},
				addRow: function(template) {
					if (_.isEmpty(templates)) {
						return false;
					}
					template = template.slice(0, 1).toUpperCase() + template.slice(1);
					var html = templates['template' + template].split('###-ticket-row-increment-###').join(String(increment));
					increment++;
					var $row = $ticketsContainer.append(html);

					fwEvents.trigger('fw:options:init', {$elements: $row});

					var values = methods.readRowValues($row);
					methods.trigger('row:added', {values: values, $element: $row});
					return true;
				},

				trigger: function (event, args) {
					$wrapper.trigger('fw:option-type:tickets:' + event, args);
					return args;
				},

				checkRows: function(){
					if ($wrapper.find('.fw-tickets-tbody .fw-ticket-row').length) {
						methods.showSecondTab();
					}else{
						methods.showFirstTab();
					}
				},

				showSecondTab: function(){
					$wrapper.find('.fw-option-tickets-tab.first').addClass('fw-tab-closed');
					$wrapper.find('.fw-option-tickets-tab.second').removeClass('fw-tab-closed');
					methods.trigger('tabs:changed', {$element: $wrapper.find('.fw-option-tickets-tab.second')})
				},

				showFirstTab: function(){
					$wrapper.find('.fw-option-tickets-tab.second').addClass('fw-tab-closed');
					$wrapper.find('.fw-option-tickets-tab.first').removeClass('fw-tab-closed');
					methods.trigger('tabs:changed', {$element: $wrapper.find('.fw-option-tickets-tab.first')})
				},

				removeRow: function($row) {
					if (confirm(removeRowConfirmMsg)) {
						var values = methods.readRowValues($row);
						$row.remove();
						methods.trigger('row:removed', {values: values});
					}
				},

				readRowValues: function($row) {
					var result = [];
					$row.find(':input').each(function(){
						result[$(this).attr('id')] = { value: $(this).val(), name: $(this).attr('name'), data: $(this).data() }
					});
					return result;
				},

				reInitSortable: function() {
					var $rows = $wrapper.find('.fw-tickets-tbody');
					try {
						$rows.sortable('destroy');
					} catch (e) {
						// happens when sortable was not initialized before
					}

					var isMobile = $(document.body).hasClass('mobile');

					$rows.sortable({
						items: '> .fw-ticket-row',
						handle: '.fw-ticket-cell',
						cursor: 'auto',
						placeholder: 'fw-ticket-row sortable-placeholder',
						delay: ( isMobile ? 200 : 0 ),
						distance: 2,
						tolerance: 'pointer',
						forcePlaceholderSize: true,
						axis: 'y',
						helper: fixHelper,
						stop: function(e, ui) {
							ui.item.removeClass( 'fw-fix-border-top' );
						},
						start: function(e, ui){
							// Update the height of the placeholder to match the moving item.
							{
								var height = ui.item.outerHeight();

								height -= 2; // Subtract 2 for borders

								ui.placeholder.height(height);
							}
						}
					});

				},

				readIncrement: function(){
					var counters = $wrapper.find('.fw-add-ticket-row').map(function() {
						return parseInt($(this).data('row-counter'));
					}).get();

					if (counters.length) {
						increment = Math.max.apply( Math, counters );
					}

					increment++;
				},

				applyEvents: function(){
					$wrapper.find('.fw-add-row-btn').on('click', function(){
						var template = $(this).data('ticket-type');
						methods.addRow(template);
						methods.reInitSortable();
					});

					$wrapper.on('click', '.fw-actions-remove-row', function(){
						methods.removeRow($(this).closest('.fw-ticket-row'));
					});

					$wrapper.on('fw:option-type:tickets:row:removed fw:option-type:tickets:row:added', function(data){
						methods.checkRows();
						return data;
					});

				},

				initialize: function(){
					methods.readIncrement();
					methods.readTemplatesHTML();
					methods.removeTemplatesHolder();
					methods.applyEvents();
					methods.reInitSortable();
					fwe.trigger('fw:options:event-tickets:initialized', {$element: $wrapper});
				}
			}

		methods.initialize();
	}

	fwe.on('fw:options:init', function (data) {
		data.$elements
			.find('.fw-option-type-tickets:not(.fw-option-initialized)').each(init)
			.addClass('fw-option-initialized');
	});

})(jQuery, _, fwEvents)