/**
 * Callback to add a product gallery product html to it's relevant section
 */
function insertProductGalleryProduct(data, originator) {
	$('.section-product-gallery').each(function() {
		if ($(this).data('id') == data.meta.section) {
			var container = $(this).children('ul.sortable');
			var exists = false;

			container.children('li').each(function() {
				if ($(this).data('id') == data.meta.product) {
					exists = true;
				}
			});

			if (!exists) {
				console.log(data.html);
				container.append(data.html);
			}
		}
	});
}

$(document).ready(function() {

	/**
	 * Update product gallery product popup picture preview. Peter piper picked...
	 */
	$('body').on('change', '#section_productgalleryproduct_type_image', function(e) {
		$('.popup-image-preview').css('background-image', 'url(\''+$(this).val()+'\')');
	});

	/** 
	 * Add product to product gallery
	 */
	$('.admin-editor').on('click', '.add-product-to-gallery .add-product .from-link', function(e) {
		e.preventDefault();
		var add_dialogue = $(this).parent();
		var add_url = add_dialogue.children('input[type=text]').val();

		if (add_url.length > 3) {
			add_dialogue.children().hide();
			add_dialogue.children('.loader').css('display', 'block');
			
			$.post('/admin/products/rest/parse-product-gallery-product/'+add_dialogue.data('productgallery-id'), {
				url:	add_url
			}).done(function(data) {
				add_dialogue.parent().children('ul').append($(data.html));
				add_dialogue.children().show();
				add_dialogue.children('.loader').css('display', 'none');
				add_dialogue.children('input[type=text]').val('');
			}).fail(function() {
				add_dialogue.children().show();
				add_dialogue.children('.loader').css('display', 'none');
			});
		}
	});

	/** 
	 * Remove product from product gallery
	 */
	$('.admin-editor').on('click', '.remove-productgalleryproduct', function(e) {
		e.preventDefault();
		$(this).parent().remove();
	});

});