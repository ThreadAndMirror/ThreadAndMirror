$(document).ready(function() {

	/**
	 * Show/hide blog comment form
	 */
	$('body').on('click', '.article .comments a.add-comment', function() {
		var commentForm = $(this).parent().parent().children('.add-comment-form');
		if (commentForm.length) {
			commentForm.slideToggle();
		}
	});

    /**
     * Blog listing masonry
     */
    //if ($('.article-list').length) {
    //    var container = document.querySelector('.article-list');
    //    window.packery = new Packery( container, {
    //        itemSelector: '.article-snippet',
    //        layoutMode: 'masonry',
    //        columnWidth: '.article-snippet',
    //        gutter: 30
    //    });
    //}

});