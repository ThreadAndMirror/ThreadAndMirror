function getPickPopup(action, offset, url)
{
	$.post(action, {
		url:    url,
		offset: offset
	}).done(function(data) {
		if (data.status == 'success') {
			var popup = $(data.html);
			$('body').append(popup);
			popup.children('.admin-popup').slideToggle();
		} else {
			if (data.flash) {
				createFlashMessage(data.status, data.message);
			}
		}
		if (data.callback) {
			window[data.callback](data, originator);
		}
	});
}

function addEditorsPickCallback(data, originator)
{
	// Add the html
	var container = $('.add-item-to-gallery ul.sortable');
	container.append(data.html);

	// Update the offset
	var adder = $('#add-pick-from-url').closest('.add-item');
	adder.data('offset', adder.data('offset') + 1);

}

$(document).ready(function() {

	// Load the pick form and pre-fill with data using a product url
	$('.admin-editor').on('click', '#add-pick-from-url', function(e) {
		e.preventDefault();
		getPickPopup($(this).attr('href'), $(this).closest('.add-item').data('offset'), $('#add-pick-url').val());
	});

	// Load the pick form
	$('.admin-editor').on('click', '#add-pick-manually', function(e) {
		e.preventDefault();
		getPickPopup($(this).attr('href'), $(this).closest('.add-item').data('offset'));
	});
});