/**
 * 
 */
(function($) {	
	$.entwine('ss', function($) {		

		$.entwine('colymba', function($) {
			

			/**
			 * Bulkselect checkbox behaviours
			 */
		    $('input.bulkSelectAll').entwine({
		      onmatch: function(){
					},
					onunmatch: function(){				
					},
		      onclick: function()
		      {
		        var state = $(this).prop('checked');
		        $(this).parents('.ss-gridfield-table')
		        			 .find('td.col-bulkSelect input')
		        			 .prop('checked', state)
		        			 .trigger('change');
		      },
		      getSelectRecordsID: function()
		      {
		      	return $(this).parents('.ss-gridfield-table')
						      				.find('td.col-bulkSelect input:checked')
						      				.map(function() {  
						      					return parseInt( $(this).data('record') )
						      				})
												  .get();
		      }
		    });
		    
		    
			
			/**
			 * Update rows
			 */
		    $('a.doBulkUpdate').entwine({
		    	onmatch: function(){
				},
				onunmatch: function(){				
				},
			    onclick: function(){
	        		//alert('Clicked');
		        	//Load bulkValues
		        	var bulkNames = [];
		        	var bulkValues = []
		        	var i = 0
		        	$(this).parents('.ss-gridfield-table').find('th.main input').each(function() {
		        		bulkName = $(this).attr('name').replace(/\[|\]/g, "");
		        		bulkNames[i] = bulkName
						if ($(this).is(':checkbox')) {
							bulkValues[i] = $(this).prop('checked')
						} else {
							bulkValues[i] = $(this).val()
						}
		        		i++
		        		//alert($(this).attr('name') + $(this).val());
		       
		        	});
		        	//Update values if row checked
			        $(this).parents('.ss-gridfield-table').find('tr.ss-gridfield-item').each(function() {
		        		//alert('Found Row');
			        	$thisRow = $(this)
			        	//alert($this.find('td.col-bulkSelect input').prop('checked'))
			        	if ($thisRow.find('td.col-bulkSelect input').prop('checked')) { //if checked apply values
			        		//alert('Row checked');
			        		$thisRow.find('td input').each(function() {
			        			for(var i=0; i<bulkValues.length; i++) {
			        				var nameToCheck = $(this).attr('name').replace(/\[|\]/g, "");
			        				if (nameToCheck.indexOf(bulkNames[i]) > -1) {
		        						//alert(bulkNames[i]+bulkValues[i])
		        						if ($(this).is(':checkbox')) {
		        							$(this).prop('checked', bulkValues[i])
		        						} else {
		        							$(this).val(bulkValues[i])
		        						}
			        				}
			        			}
			        			
			        		});
			        		$thisRow.find('td.col-bulkSelect input').prop('checked', false)
			        		$('input.bulkSelectAll').prop('checked', false)
			        	}
			        });
			    }
		    });
			
		});	
	});
}(jQuery));