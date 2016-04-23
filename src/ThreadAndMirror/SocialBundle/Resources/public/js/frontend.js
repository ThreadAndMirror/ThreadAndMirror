$(document).ready(function() {
	
	if ($('.social-circle-feed').length) {
		var container = document.querySelector('.social-circle-feed');
		window.packery = new Packery( container, {
			itemSelector: '.social-circle-feed__post',
			layoutMode: 'meticulous',
			columnWidth: '.social-circle-feed__post--1x1'
        });
	}
	
	/**
	 * Add to my social circle
     */
	$('.social-circle-feed').on('click', '.add-to-my-circle', function(e) {
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