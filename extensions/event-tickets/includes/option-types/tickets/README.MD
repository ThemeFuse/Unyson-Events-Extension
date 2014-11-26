Option Type
===========

```
'unique_id' => array(
	'type' => 'tickets',
	'desc' => false,
	'label' => false,
	'header-controls' => array(
		'name' => array(                                                                  //required unique string key
			'title'    => __('Column Title 1', 'fw'),                                     //optional string column title
			'class'    => 'required any-random-css-class-1',                              //optional string column title styling class
		),
		'quantity' => array(
			'title'    => __('Column Title 2', 'fw'),
			'class'    => 'required any-random-css-class-2',
		),
		'price' => array(
			'title' => __('Column title 3', 'fw'),
		),
		'other_unique_column_id' => array(
			'title' => 'asd'
		)
	'row-controls' => array(
		'free' => array(                                                                    //any unique id (ticket type)
			'button' => array(                                                              //optional array with addable button's conrol
				'title' => __('Free Ticket', 'fw'),                                         //optional string|false  title (false - disable title, default title is array key on level up )
				'class' => 'btn button-primary button-large'                                //optional string|false  add row button class styles
			),
			'popup-controls' => array(                                                      //optional array setting for popup button in the 'actions' column
				'title' =>  __('Random Title', 'fw'),                                       //optional string popup window title
				'options' => array(                                                         //optional array of options
					'unique_id' => array(
						'type' => 'text'
						'label' => __('Demo label', 'fw')
						'desc' => __('Demo desc', 'fw')
						'help' => __('Demo help', 'fw')                                     //optional string tooltip text/html
					)
					/** other options **/
				)
			'row-options' => array(
				'name' => array(                                                            //key is required by header-controls column id
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
				'other_unique_column_id' => array(
					/** any simple option **/
				)
			)
		/** other row types **/
	)

	)


	fw:options:event-tickets:initialized - trigger after option initialized {$element} //$element - curently initialized ticket option

	fw:option-type:tickets:row:added  - trigger after row was added {e,$element,values} //$element - currently added row| values - default values

	fw:option-type:tickets:row:removed - trigger after row was removed {e, values} //values - val from removed row

	fw:option-type:tickets:tabs:changed - trigger after tabs was changed {$element} //$element - currently active tab