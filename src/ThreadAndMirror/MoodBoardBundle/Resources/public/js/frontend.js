$(document).ready(function() {
	
	if ($('.MoodBoard-feed').length) {
		var container = document.querySelector('.MoodBoard-feed');
		window.packery = new Packery( container, {
			itemSelector: 'figure',
			layoutMode: 'meticulous',
			columnWidth: 'figure.text-post',
		});
	}
	
	/**
	 * Add to my MoodBoard circle
     */
	$('.MoodBoard-feed').on('click', '.add-to-my-circle', function(e) {
		e.preventDefault();
		var button = $(this);

		if ($('body.not-logged-in').length) {
			alert('Please sign in to begin adding to your MoodBoard circle.');
		} else {
			$.get('/rest/MoodBoard/favourite-feed/'+button.data('id')).done(function(data) {
				createFlashMessage(data.status, data.message);

				if (data.status == 'success') {
					button.addClass('favourited');
				}
			});
		}
	});
});