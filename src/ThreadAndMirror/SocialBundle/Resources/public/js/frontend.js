$(document).ready(function() {
	
	/**
	 * Isotope init
	 */
	if ($('.social-feed').length) {
		$('.social-feed').isotope({
			itemSelector: 'figure',
			layoutMode: 'masonry',
			columnWidth: 'figure.text-post',
			getSortData: {
			  	name: '.name',
			  	symbol: '.symbol',
			  	number: '.number parseInt',
			  	category: '[data-category]',
			}
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