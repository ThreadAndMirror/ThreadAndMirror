$(document).ready(function() {
	
	if ($('.social-feed').length) {
		var container = document.querySelector('.social-feed');
		window.packery = new Packery( container, {
			itemSelector: 'figure',
			layoutMode: 'meticulous',
			columnWidth: 'figure.text-post'
        });
	}
	
	/**
	 * Add to my social circle
     */
	$('.social-feed').on('click', '.add-to-my-circle', function(e) {
		e.preventDefault();
		var button = $(this);

		if ($('body.not-logged-in').length) {
			alert('Please sign in to begin adding to your social circle.');
		} else {
			$.get('/rest/social/favourite-feed/'+button.data('id')).done(function(data) {
				createFlashMessage(data.status, data.message);

				if (data.status == 'success') {
					button.addClass('favourited');
				}
			});
		}
	});
});