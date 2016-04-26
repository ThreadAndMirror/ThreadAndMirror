

$('.header-bar__show-nav').on('click', function() {
   $('.header-bar__nav').slideToggle();
});

/**
 * Voting complete callback
 */
function votingComplete(data, originator) {
	originator.parent().parent().parent().children('.description').append('<p class="message">'+data.message+'</p>');
	originator.parent().parent().parent().children('.choices').html(data.html);
}

/**
 * Generic mark as added callback, simply puts a class on the originating element if it's rest request was successful
 */
function markAsAdded (data, originator) {
	if (data.status == 'success') {
		originator.addClass('added');
	}
}

/**
 * Generic mark as remove callback, removes the container parent element
 */
function markAsRemoved (data, originator) {
	if (data.status == 'success') {
		originator.parent().remove();
	}
}

/**
 * Make a generic slider that automatically cycles through slides, configurable with options:
 *
 * transitionTime: The time it takes for the slide change to animate in milliseconds
 * waitTime: Time in milliseconds between each slide showing
 */
$.fn.autoSlider = function(options) {
	this.slides = $(this).find('.slide');
	this.currentSlide = 1;
	this.maxSlides = this.slides.length;
	this.nextSlide = 2;
	this.transitionTime = 1000;
	this.waitTime = 5000;

	// Configure the options
	if (options.hasOwnProperty('transitionTime')) {
		this.transitionTime = options.transitionTime;
	}
	if (options.hasOwnProperty('waitTime')) {
		this.waitTime = options.waitTime;
	}

	// Animate
	animateSlider = function() {
		if (this.currentSlide != this.maxSlides) {
			this.nextSlide = this.currentSlide + 1;
		} else {
			this.nextSlide = 1;
		}

		$(this.slides).eq(this.currentSlide-1).fadeOut(this.transitionTime);
		$(this.slides).eq(this.nextSlide-1).fadeIn(this.transitionTime);

		setTimeout(function() {
			this.animateSlider();
		}, this.waitTime);
	}

	setTimeout(function() {
		this.animateSlider();
	}, this.waitTime);
}


$(document).ready(function() {

	/**
	 * Back to Top Button
	 */
	$('#back-to-top').on('click', function() {
		$('html, body').animate({ scrollTop: 0 });
	});

	/**
	 * Homepage slider
	 */
	if($('#slider').length) {
		setTimeout(function() {
			animateSlider(current_slide_number);
		}, 5000);
	}

	/**
	 * Instagram slider
	 */
	if($('#instagram-slider').length) {
		setTimeout(function() {
			animateInstagramSlider(ig_current_slide_number);
		}, 3000);
	}

	/**
	 * Autosliders - fading
	 */
	if ($('.auto-slider-fading').length) {
		$('.auto-slider-fading').each(function(){
			$(this).autoSlider({ style: 'fading' });
		});
	}

	/**
	 * Clear text search filter
	 */
	search_form = $('.text-search');
	search_field = $('.text-search input');
	search_clear = $('.text-search div.clear-text');

	if (search_form.length) {
		search_clear.on('click', function(e){
			e.preventDefault();
			search_field.val('');
			search_form.submit();
		});
	}
	
	/**
	 * Show/hide clear button
	 */
	if (search_form.length) {

		if (search_field.val().length > 0) {
			search_clear.show();
		}

		search_field.on('keyup', function(e){
			if (search_field.val().length > 0) {
				search_clear.show();
			} else {
				search_clear.hide();
			}
		});
	}

	/**
	 * Homepage slider
	 */
	var current_slide_number = 1, amount_of_slides = $('#slider .slide').length, next_slide_number;

	function animateSlider(slide) {
		if (slide != amount_of_slides) {
			next_slide_number = slide + 1;
		} else {
			next_slide_number = 1;
		}
		$('#slider div.slide:nth-child('+slide+')').fadeOut(1000);
		$('#background div.background-image:nth-child('+slide+')').fadeOut(1000);
		$('#slider div.slide:nth-child('+next_slide_number+')').fadeIn(1000);
		$('#background div.background-image:nth-child('+next_slide_number+')').fadeIn(1000);

		setTimeout(function() {
			animateSlider(next_slide_number);
		}, 5000);
	}

	var ig_current_slide_number = 1, ig_amount_of_slides = $('#instagram-slider .slides .slide').length, ig_next_slide_number;

	/**
	 * Instagram widget fading slider
	 */
	function animateInstagramSlider(slide) {
		if (slide != ig_amount_of_slides) {
			ig_next_slide_number = slide + 1;
		} else {
			ig_next_slide_number = 1;
		}
		$('#instagram-slider .slides .slide').eq(slide-1).fadeOut(1000);
		$('#instagram-slider .slides .slide').eq(ig_next_slide_number-1).fadeIn(1000);

		setTimeout(function() {
			animateInstagramSlider(ig_next_slide_number);
		}, 5000);
	}




	/**
	 * Create flash message
	 */
	function createFlashMessage(type, message) {

		// remove any existing messages
		$('.flash').remove();

		// create the new message
		var element = $('<div class="flash flash-'+type+'"><p>'+message+'</p><i class="fa close">&times;</i></div>');
		$('#flash-messages > .wrap').append(element);

		// auto hide after 5 seconds
		setTimeout(function() {
			element.slideToggle();
		}, 5000);
	}

	/**
	 * Close flash message
	 */
	$('body').on('click', '.flash .close', function(){
		$(this).parent().slideToggle();
	});

	/**
	 * Standardised get request
	 */
	$('body').on('click', '.rest-get-request', function(e) {
		e.preventDefault();
		var originator = $(this);

		$.get(originator.data('url')).done(function(data) {
			if (data.flash) {
				createFlashMessage(data.status, data.message);
			}
			if (data.callback) {
				window[data.callback](data, originator);
			}
		});
	});
	
	/**
	 * Standardised post request
	 */
	$('body').on('click', '.rest-post-request', function(e) {
		e.preventDefault();
		var originator = $(this);
		var form = originator.parent();
		var buttonText = originator.html();
		
		originator.html('<i class="fa fa-circle-o-notch fa-spin"></i>');

		$.post(form.attr('action'), form.serialize()).done(function(data) {
			originator.html(buttonText);

			if (data.flash) {
				createFlashMessage(data.status, data.message);
			}

			if (data.callback) {
				var callback = data.callback;
				eval(callback)(data, originator);
			}
		});
	});

	/**
	 * Load more items, uses offset and api url data stored on the loading buffer element to get more items and append them
	 */
	var y;
	var currently_loading = false;
	var loader_buffer = $('.rest-load-more');
	var chunk_size = loader_buffer.data('offset');

	$(window).scroll(function(){
		if (!currently_loading && loader_buffer.length) {

			// Check whether the loader is a packery layout
			if (typeof this.packery !== 'undefined') {
				var packery = this.packery;
			} else {
				var packery = false;
			}

			// get our current offset and the y scroll position
			total_loaded = loader_buffer.data('offset');

			y = $(window).scrollTop();

			// only load more when we reach the trigger point
			if (y > (loader_buffer.offset().top - 2000)) {
				currently_loading = true;

				// do the rest request to the url determined by the buffer element
                $.get(loader_buffer.data('url').replace('offset', total_loaded)).done(function(data) {

					// update the list length data
					loader_buffer.data('offset', total_loaded+chunk_size);
					currently_loading = false;

					// check if we're loading into an isotope frame, as these are handled differently
					if (packery) {
						$('#packery-loader').append($(data.html));
						packery.reloadItems();
						packery.layout();
					} else {
						loader_buffer.before(data.html);
					}

					// stop attempt to load more if the controller tells us there's nothing left
					if (data.callback == 'stopLoading') {
						loader_buffer.remove();
					}
				});
			}
		}
	});

	////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////// Product Views /////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////

	// Show product filter

	var filter_panel = $('.product-filter');
	var product_grid = $('.product-grid');

	$('#show-product-filter').on('click', function() {
		if ($(this).hasClass('hide')) {
			$(this).removeClass('hide');
			filter_panel.animate({
				width: '0px'
			}, 300, function(){
				product_grid.css('width', '960px');
			});
		} else {
			$(this).addClass('hide');
			filter_panel.animate({
				width: '189px'
			}, 300);
			product_grid.css('width', '770px');
		}
	});

	// Expand/hide filter options

	$('.product-filter h5').on('click', function() {
		if ($(this).hasClass('minimised')) {
			$(this).removeClass('minimised');
			$(this).children('i').addClass('fa-angle-down').removeClass('fa-angle-right');
		} else {
			$(this).addClass('minimised');
			$(this).children('i').addClass('fa-angle-right').removeClass('fa-angle-down');
		}
		$(this).parent().children('.filter-options').slideToggle();
	});

	// Select all options

	$('.product-filter .select-all').on('click', function(e) {
		e.preventDefault();
		$(this).parent().find('input[type=checkbox]').prop('checked', true);
	});

	// Clear all options

	$('.product-filter .clear-all').on('click', function(e) {
		e.preventDefault();
		$(this).parent().find('input[type=checkbox]').prop('checked', false);
	});

	// Save favourite filters

	$('#save-favourite-filters').on('click', function() {
		$.get('/rest/save-favourite-filters').done(function(data) {
			if (data.success) {
				alert(data.message);
			} else {
				alert(data.message);
			}
		});
	});

	// View product

	// var view_product_dialogue = $('.view-product-dialogue');
	// var view_product_dialogue_close =  $('.view-product-dialogue .close');
	// var container = $('.view-product-dialogue .container');
	// var view_loaded = false;

	// $('.product').on('click', 'a.view', function(e) {
	// 	e.preventDefault();
	// 	container.addClass('ajax-loader');
	// 	container.html('');
	// 	view_loaded = true;
	// 	product_grid.css('height', '960px');
	// 	$(window).scrollTop(150);
	// 	view_product_dialogue.show();

	// 	$.get('/rest/view-product/'+$(this).data('pid')).done(function(data) {
	// 		if (data.success) {
	// 			container.html(data.html);
	// 			product_grid.css('height', container.height+'px');
	// 			container.removeClass('ajax-loader');
	// 		} else {

	// 		}
	// 	}).fail(function() {
	// 		view_product_dialogue.fadeOut();
	// 	});
	// });

	// var y = $(window).scrollTop();

	// view_product_dialogue_close.on('click', function() {
	// 	product_grid.css('height', 'auto');
	// 	view_product_dialogue.hide();
	// 	$(window).scrollTop(y);
	// 	view_loaded = false;
	// });

	// View product frame scroller (and universal scroll position logger)

	// Add to wishlist

	$('.product').on('click', '.add-to-wishlist', function(e) {
		e.preventDefault();
		var button = $(this);

		if ($(this).hasClass('logged-in')) {
			$.get('/rest/add-to-wishlist/'+button.data('pid')).done(function(data) {
				if (data.success) {
					alert(data.message);
				}
			});
		} else {
			window.location = '/wishlist';
		}
	});

	// Remove from wishlist

	$('.product').on('click', '.remove-from-wishlist', function(e) {
		e.preventDefault();
		var button = $(this);
		confirm('Are you sure you want to remove this product from your wishlist?');
		
		$.get('/rest/remove-from-wishlist/'+button.data('id')).done(function(data) {
			
			if (data.success) {
				button.parent().remove();
				//alert(data.message);
			} else {
				alert(data.message);
			}
		});
	});

	// Add to outfit

	$('.add-to-outfit').on('mouseover', function(e) {
		$(this).parent().animate({
			height: '240px'
		}, 200);
	});
	$('.add-to-outfit-options').on('mouseleave', function(e) {
		$(this).animate({
			height: '20px'
		}, 200);
	});
	$('.add-to-outfit-options').on('click', 'a', function(e) {
		e.preventDefault();
		var button = $(this);

		$.get($(this).attr('href')).done(function(data) {
			if (data.success) {
				alert(data.message);
			}
		});
	});

	// Cache lightbox elements

	var lightbox = $('#lightbox');
	var lightbox_dialogue_header = $('#lightbox > .lightbox-dialogue > h4');
	var lightbox_dialogue_container = $('#lightbox > .lightbox-dialogue > .container');

	// Add product from url

	$('#add-from-url').on('click', function() {
		if($(this).hasClass('hide')) {
			$('.add-from-url-dialogue').stop().fadeOut();
			$(this).removeClass('hide');
		} else {
			$('.add-from-url-dialogue').stop().fadeIn();
			$(this).addClass('hide');
		}
	});
	$('body').on('click', '#add-from-url-submit', function() {
		$.post('/rest/add-product-from-url', {
			url: $('#add-from-url-url').val()
		}).done(function(data) {
			if (data.success) {
				alert(data.message);
				$('.add-from-url-dialogue').fadeOut();
				$(this).removeClass('hide');
			} else {
				$('.add-from-url-dialogue p.error').remove();
				$('.add-from-url-dialogue').append('<p class="error">'+data.message+'</p>');
			}
		});
	});

	// Add product from url

	$('#add-new-outfit').on('click', function() {
		if($(this).hasClass('hide')) {
			$('.add-outfit-dialogue').stop().fadeOut();
			$(this).removeClass('hide');
		} else {
			$('.add-outfit-dialogue').stop().fadeIn();
			$(this).addClass('hide');
		}
	});

	////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////// Blog //////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////

	// Scrolling sidebar 

	sidebar_container = $('.sidebar .snap');

	function scrollingSidebar() { 
		var y = $(window).scrollTop();

		if (sidebar_container) {
			var top_clip = 0;
			var bottom_clip = $('.layout-sidebar').height()-sidebar_container.height()-40;

			if (y < bottom_clip) {
				sidebar_container.css('margin-top', (y-top_clip)+'px');
			} else {
				sidebar_container.css('margin-top', bottom_clip+'px');
			}
		}
	}

	scrollingSidebar();

	$(window).scroll(function(){
		scrollingSidebar();
	});

	// Load more articles ajax

	// var currently_loading = false;

	// $(window).scroll(function(){

	// 	if (!currently_loading) {
	// 		var loader_buffer = $('.load-more-articles');

	// 		if (loader_buffer.length) {

	// 			total_loaded = loader_buffer.data('offset');
	// 			var y = $(window).scrollTop();
	// 			last_article = $('.article').eq(total_loaded-1);

	// 			if (y > last_article.offset().top) {
	// 				currently_loading = true;

	// 				$.get('/rest/get-more-posts/'+total_loaded).done(function(data) {
	// 					loader_buffer.data('offset', total_loaded+3);
	// 					loader_buffer.before(data.html);
	// 					currently_loading = false;

	// 					// add gallery prototypes to all the new galleries
	// 					$('.gallery-layout-feature').each(function() {
	// 						$(this).featureImageGallery();
	// 					});

	// 					if (data.stopLoading) {
	// 						loader_buffer.remove();
	// 					}
	// 				});
	// 			}
	// 		}
	// 	}
	// });

	// Feature style image galleries

	$.fn.featureImageGallery = function() {
		$(this).data('features', $(this).find('.section-product-gallery-feature__product'));
		if (!$(this).data('currentImage')) {
			$(this).data('currentImage', 1);
		}
	};

	$.fn.nextImage = function() {
		if ($(this).data('currentImage') == $(this).data('maxImages')) {
			$(this).updateImage(1);
		} else {
			$(this).updateImage($(this).data('currentImage')+1);
		}
	};

	$.fn.prevImage = function() {
		if ($(this).data('currentImage') == 1) {
			$(this).updateImage($(this).data('maxImages'));
		} else {
			$(this).updateImage($(this).data('currentImage')-1);
		}
	};

	$.fn.updateImage = function(slide) {
		$(this).data('currentImage', slide);
		var self = $(this);
		$(this).data('features').hide().each(function(){
			if ($(this).data('image') == self.data('currentImage')) {
				$(this).show();
			}
		});
	};

	$('.section-product-gallery').each(function() {
		$(this).featureImageGallery();
	});

	$('body').on('click', '.section-product-gallery-feature__next', function() {
		$(this).closest('.section-product-gallery').nextImage();
	});

	$('body').on('click', '.section-product-gallery-feature__prev', function() {
		$(this).closest('.section-product-gallery').prevImage();
	});

	$('body').on('click', '.section-product-gallery-feature__thumbnail-product', function() {
		$(this).closest('.section-product-gallery').updateImage($(this).data('image'));
	});

	////////////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////// Dialogue Controls /////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////

	var product_view = $('.product-view');

	// Add size to wishlist

	$('.logged-in, .not-logged-in').on('click', '.sizes .available', function(e) {
		e.preventDefault();
		window.open($('.actions .buy').attr('href'));
	});

	$('.logged-in').on('click', '.sizes .unavailable', function(e) {
		e.preventDefault();
		var button = $(this);

		$.post('/rest/add-to-wishlist/'+product_view.data('pid'), {
			size: $(this).data('size')
		}).done(function(data) {
			if (data.success) {
				createFlashMessage('success', data.message);
			} else {
				createFlashMessage('error', data.message);
			}
		});
	});

	$('.not-logged-in').on('click', '.sizes .unavailable', function(e) {
		alert('You need to be logged in to set up e-mail alerts!');
	});

});