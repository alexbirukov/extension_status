jQuery(function() {
	var $ = jQuery;
	
	var table = $('#contents table');
	var context = $('#context');
	
	table.find('thead th:last').after('<th>Status</th>');
	
	table.find('tbody tr').each(function() {
		$(this).find('td:last').after('<td class="status"></td>');
	});
	
	context.append('<ul class="actions"></ul>');
	context.find('ul.actions').append('<li><a href="#" class="button updates">Check For Updates</a></li>');
	
	context.find('a.updates').on('click', function(e) {
		e.preventDefault();
		
		table.find('tbody tr').each(function() {
			var row = $(this);
			var status = row.find('td.status');
			status.empty();
			
			var id = row.find('input[type="checkbox"]').attr('name').replace(/(items\[)([a-z0-9-_]+)(\])/gi, '$2');
			
			$.ajax({
				url: Symphony.Context.get('root') + '/symphony/extension/extension_status/proxy/?id=' + id,
				dataType: 'xml',
				success: function(response) {
					response = $(response).find('response');
					if(response.attr('error') == '404') {
						status.text('Not found');
					}
					else if(response.attr('can-update') == 'yes') {
						status.html('Update available (<a href="' + response.attr('latest-url') + '">' + response.attr('latest') + "</a> or <a href='" + response.attr('extension-url') + "#changelog'>more information</a>)");
					}
					else if(response.attr('compatible-version-exists') == 'no') {
						status.text('Incompatible with Symphony ' + response.attr('symphony-version'));
						status.addClass('inactive');
					} else {
						status.text('Using latest version (' + response.attr('latest') + ')');
						status.addClass('inactive');
					}
				}
			})

		});
		
	});
	
});