/**
 * Update the col span of the layout editor
 */
function updateLayoutEditorSpan(span) {

    //$('.layout-editor section').each(function(){
    //    if (($(this).position().top + $(this).height() + 30) > height) {
    //        height = $(this).position().top + $(this).height() + 30;
    //    }
    //});

    $('.layout-editor').removeClass('col-2 col-4').addClass('col-'+span);
}

$(document).ready(function() {

    /**
     * Initialise draggable on section
     */
    function intiliaseDraggable(section) {
        if ($('.layout-editor.col-2').length) {
            section.draggable({
                grid: [ 480, 15 ]
            });
        } else {
            section.draggable({
                grid: [ 240, 15 ]
            });
        }
    }

    $('.layout-editor section').each(function(){
        intiliaseDraggable($(this));
    });

	/** 
	 * Add section to blog post
	 */
	$('.available-sections').on('click', 'a', function(e){
		e.preventDefault();
		var originator =  $(this);
		var buttonText = originator.html();
		var buttonWidth = originator.css('width');

		originator.css('width', buttonWidth);
		originator.html('<i class="fa fa-circle-o-notch fa-spin"></i>');

		$.get('/admin/blog/rest/add-section-type/'+$(this).data('type-id')).done(function(data) {
			var section = $(data.html);

            intiliaseDraggable(section);

			$('.layout-editor').append(section);
			originator.html(buttonText);
            section.css('top', $('.layout-editor').css('height'));
            section.find('.section-y').val(section.position().top);
            updateLayoutEditorHeight();
		});
	});

    /**
     * Resizable sections
     */
    $('section.unpinned').draggable({
        grid: [ 5, 5 ]
    }).resizable({
        aspectRatio: true,
        grid: [ 5, 5 ],
        handles: 'se',
        containment: $('.admin-editor')
    });

	/** 
	 * Remove section from blog post
	 */
	$('.admin-editor').on('click', 'a.remove-section',  function(e){
		e.preventDefault();
		var button = $(this);
		confirm('Are you sure you want to delete this section?');

		$.get('/admin/blog/rest/remove-section/'+button.data('id')).done(function(data) {
			if (data.status == 'success') {
				button.closest('section').remove();
                updateLayoutEditorHeight();
			} else {
				createFlashMessage(data.status, data.message);
			}
		});
	});

    /**
     * Increase section column span
     */
    $('.admin-editor').on('click', 'a.column-span',  function(e){

        e.preventDefault();
        var className = 'col-' + $(this).text();
        $(this).closest('section').removeClass('col-1 col-2').addClass(className).find('.section-span').val($(this).text());

        updateLayoutEditorHeight();
    });

    /**
     * Change section heading style
     */
    $('.admin-editor').on('click', 'a.heading-style',  function(e){

        e.preventDefault();
        var section = $(this).closest('section');
        var preview = section.children('.preview');
        var value = $(this).text();

        preview.children().last().changeElementType(value);
        preview.children('textarea').removeClass('h4 h6').addClass(value);
        section.find('input.section-style').val(value);

        updateLayoutEditorHeight();
    });

    /**
     * Change section alignment
     */
    $('.admin-editor').on('click', 'a.alignment',  function(e){

        e.preventDefault();
        var value = $(this).data('value');
        var className = 'alignment-' + value;
        var section = $(this).closest('section');

        section.removeClass('alignment-left alignment-center alignment-right').addClass(className);
        section.find('input.section-alignment').val(value);
        section.find('.selected-alignment > i').removeClass().addClass('fa fa-align-' + value);

        updateLayoutEditorHeight();
    });

    /**
     * Change section image effect
     */
    $('.admin-editor').on('click', 'a.image-effect',  function(e){

        e.preventDefault();
        var value = $(this).data('value');
        var className = 'css-filter-' + value;
        var section = $(this).closest('section');

        section.removeClass('css-filter-sepia css-filter-none css-filter-grayscale css-filter-cutout').addClass(className);
        section.find('input.section-effect').val(value);
    });

	/** 
	 * Update the image on the blog sections
	 */
	$('.admin-blog-editor').on('change', '.feature-image', function(e){
		var header_section = $('.section-blog-header .image');
		if (header_section.length) {
			header_section.css('background-image', $(this).css('background-image'));
		} else {
			var image = $('<div class="image" style="background-image: url('+$(this).css('background-image')+')></div>');
			$('.section-blog-header').prepend(image);
		}
	});

    /**
     * Unpin section
     */
    $('.admin-editor').on('click', 'a.unpin-section',  function(e){

        e.preventDefault();
        var section = $(this).closest('section');
        section.css('height', section.height()+'px');
        section.addClass('unpinned');
        section.draggable({
            grid: [ 5, 5 ]
        }).resizable({
            aspectRatio: true,
            grid: [ 5, 5 ],
            handles: 'se',
            containment: $('.admin-editor')
        });

        $(this).removeClass('unpin-section').addClass('pin-section').html('<i class="fa fa-thumb-tack"></i>');
        section.find('input.section-pinned').val(0);
        section.find('input.section-width').val(section.width());
        section.find('input.section-height').val(section.height());
    });

    /**
     * Update position on resize
     */
    $('.admin-editor').on('resizestop', 'section.unpinned', function() {
        var section = $(this);
        section.find('input.section-width').val(section.width());
        section.find('input.section-height').val(section.height());
    });

	/**
	 * Update headings on update
	 */
	$('.layout-editor').on('keyup', '.section-heading textarea', function() {
		$(this).siblings().last().html($(this).val());
	});

	/**
	 * Update text sections on update
	 */
	$('.layout-editor').on('keyup', '.section-text textarea', function() {
		$(this).siblings('p').html($(this).val());
	});

    /**
     * Update wysiwig sections on change
     */
    $('.layout-editor').on('keyup', '.wysiwig-editor', function() {
        $(this).siblings('.wysiwig-preview').html($(this).val());
    });

    /**
     * Add new item to list section
     */
    $('.layout-editor').on('click', '.section-list .add-list-item', function() {
        var section = $(this).closest('section');
        var index = section.find('li').length - 1;
        var item = $('<li><textarea id="section_list_type_items_'+index+'" name="420_section_list_type[items]['+index+']" class="wysiwig-editor"></textarea> <p class="wysiwig-preview"></p></li>');

        section.find('.preview ul').append(item);
    });
});