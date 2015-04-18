$(document).ready(function() {
	/**
	 * Newsletter signup
     */
	$('#newsletter-signup').on('submit', function(e) {

		e.preventDefault();
		var button = $(this).children('button');

        $.post('/rest/newsletter-signup', {
            email: button.siblings('input[name=email]').val()
        }).done(function(data) {

            if (data.status == 'success') {
                button.addClass('favourited');
                $('#newsletter-signup').html('<p>Thanks for registering your interest!</p>');
            } else {
                button.siblings('input[name=email]').css('border-color', '#AA0000');
                alert('Please enter a valid e-mail address!');
            }
        });
	});
});