$(document).ready(function() {

	// Create a pick and load the form
	$('.feature-top-controls').on('click', '.load-editors-pick', function(e) {
		e.preventDefault();
		var add_pid = $(this).parent().children('input.pid').val();
		var add_url = $(this).parent().children('input.url').val();
			
		$.post('/admin/editors-picks/rest/generate-pick', {
			url:	add_url,
			pid: 	add_pid
		}).done(function(data) {
			if (data.success) {
				$('.form-container').html(data.html);
			} else {
				$('.form-container').html('<p>'+data.message+'</p>');
			}
		});
	});

});